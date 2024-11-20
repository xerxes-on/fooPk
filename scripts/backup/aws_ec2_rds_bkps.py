import boto3
import botocore
import sys
import datetime
import os
import multiprocessing

def aws_ec2_ami_images(aws_region, deployment_environment, date):
    start_time = datetime.datetime.now()
    image_ids = []
    tags = []
    ami_instances = {}
    ec2 = boto3.resource('ec2', region_name=aws_region)
    instances = ec2.instances.filter(
        Filters=[{'Name': 'instance-state-name', 'Values': ['running']}])

    for instance in instances:
        tags = instance.tags
        ami_instances[instance.id] = {}
        for i in tags:
            key = i['Key']
            value = i['Value']
            if key.lower() == 'environment' and value.lower() == deployment_environment:
                ami_instances[instance.id]['environment'] = deployment_environment
            elif key.lower() == 'name':
                ami_instances[instance.id]['name'] = value.lower()

    for i in ami_instances:
        if 'environment' in ami_instances[i] and ami_instances[i]['environment'] == deployment_environment:
            print(f'EC2:\tCreating AMI image: ami-{ami_instances[i]["name"]}_{i}_backup-before-deployment-{date}')
            image = instance.create_image(InstanceId=i, NoReboot=True, Name=f'''ami-{ami_instances[i]['name']}_{i}_backup-before-deployment-{date}''')
            image_ids.append(image.id)
    # Waiting For Images Using Paginators
    ec2_client = boto3.client('ec2', region_name=aws_region)
    waiter = ec2_client.get_waiter('image_available')
    try:
        waiter.wait(Filters=[{
            'Name': 'image-id',
            'Values': image_ids
        }])
    except botocore.exceptions.WaiterError as error:
        print(f'EC2:\terror: {error}')
        sys.exit(101)

    print(f'EC2:\tAMI(s) creation time: {datetime.datetime.now() - start_time}')

def aws_rds_snapshot(aws_region, deployment_environment, date):
    start_time = datetime.datetime.now()
    snapshots = []
    rds = boto3.client('rds', region_name=aws_region)
    db_instances = rds.describe_db_instances()
    for instance in db_instances['DBInstances']:
        for tagList in instance['TagList']:
            if tagList['Key'].lower() == 'environment' and tagList['Value'].lower() == deployment_environment:
                print(f'''RDS:\tDB Identifier:\t{instance['DBInstanceIdentifier']}\n*\tDB Instance class:\t{instance['DBInstanceClass']}\n*\tDB engine:\t{instance['Engine']}\n*\tDB status:\t{instance['DBInstanceStatus']}''')
                response = rds.create_db_snapshot(DBInstanceIdentifier = instance['DBInstanceIdentifier'], DBSnapshotIdentifier = f'''{deployment_environment}-db-backup-{instance['DBInstanceIdentifier']}-deployment-{date}''')
                snapshots.append(response['DBSnapshot']['DBSnapshotIdentifier'])
                print(f'RDS:\t{response}')
    # Waiting For Snapshot(s) Using Paginators
    waiter = rds.get_waiter('db_snapshot_available')
    try:
        waiter.wait(Filters=[{'Name': 'db-snapshot-id', 'Values': snapshots}])
    except botocore.exceptions.WaiterError as error:
        print(error)
        sys.exit(101)
    print(f'RDS:\tSnapShot(s) creation time: {datetime.datetime.now() - start_time}')


if __name__ == "__main__":
    try:
        bkp_environment = os.environ['DEPLOY_ENVIRONMENT']
        print(f"Deployment environment:\t{bkp_environment}")
    except KeyError:
        bkp_environment = 'production'

    try:
        aws_region = os.environ['DEPLOY_AWS_REGION']
        print(f"Deployment AWS region:\t{aws_region}")
    except KeyError:
        aws_region = 'eu-central-1'

    date = datetime.datetime.now()
    release_date = date.strftime("%d%m%Y%H%M")
    snapshots = []

    # creating processes
    p1 = multiprocessing.Process(target = aws_ec2_ami_images, args = (aws_region, bkp_environment, release_date))
    p2 = multiprocessing.Process(target = aws_rds_snapshot, args = (aws_region, bkp_environment, release_date))

    # Start the backup prcesses
    p1.start()
    p2.start()

    # Wait for backup prcesses to finish
    p1.join()
    p2.join()

    print(f'Backup:\tTotal time: {datetime.datetime.now() - date}')
