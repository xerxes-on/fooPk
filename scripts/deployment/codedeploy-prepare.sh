#!/bin/bash
set -e

# Prepares for the deployment. Called from CodeDeploy's appspec.yml.
if [ "$DEPLOYMENT_GROUP_NAME" == "prod-foodpunk-static" ];
then
    FILE_DEPLOYMENT_TRIGGER=/srv/foodpunk_laravel_v2/storage/app/public/deployment.txt
    FILE_DEPLOYMENT_READY=/srv/foodpunk_laravel_v2/storage/app/public/deployment_ready.txt

    if [[ -f "$FILE_DEPLOYMENT_READY" ]]; then
        echo "File $FILE_DEPLOYMENT_READY exists"
        rm -f "$FILE_DEPLOYMENT_READY"
    fi

    touch $FILE_DEPLOYMENT_TRIGGER

    while [ -f "$FILE_DEPLOYMENT_TRIGGER" ]
    do
        echo "The file $FILE_DEPLOYMENT_TRIGGER still exists ..."
        sleep 3
    done

    echo "Check $FILE_DEPLOYMENT_READY file - DEPLOYMENT_READY trigger"
    if [[ -f "$FILE_DEPLOYMENT_READY" ]]; then
        date
        echo "File $FILE_DEPLOYMENT_READY exists"
        echo Stoping supervisor
        supervisorctl stop all
    else
        date
        while [ ! -f "$FILE_DEPLOYMENT_READY" ]
        do
            echo "File $FILE_DEPLOYMENT_READY still does not exist ..."
            sleep 3
        done
        date
        if [[ -f "$FILE_DEPLOYMENT_READY" ]]; then
            echo "File $FILE_DEPLOYMENT_READY exists"
        else
            echo "Something went wrong. Exiting ..."
            exit 10
        fi
    fi
fi

# Staging preparations
if [ "$DEPLOYMENT_GROUP_NAME" == "staging-foodpunk" ];
then
    echo "Launch Application Deployment: AWS CodeDeployment preparation:"
    sudo usermod -a -G www-data ubuntu
    rm -Rf /srv/foodpunk_laravel_v2/
    mkdir /srv/foodpunk_laravel_v2/
    chown -R www-data:www-data /srv/foodpunk_laravel_v2/
    chmod -R 775 /srv/foodpunk_laravel_v2/
    touch /tmp/deployment-cleared
fi

# Production preparations
if [ "$DEPLOYMENT_GROUP_NAME" == "prod-foodpunk-static" ] || [ "$DEPLOYMENT_GROUP_NAME" == "prod-foodpunk" ];
then
    echo "Launch Application Deployment: AWS CodeDeployment preparation:"
    # aws s3 cp s3://foodpunk-bitbucket-pipelines/.env /tmp/.env
    sudo usermod -a -G www-data ubuntu
    rm -Rf /srv/foodpunk_laravel_v2/
    mkdir /srv/foodpunk_laravel_v2/
    chown -R www-data:www-data /srv/
    chmod -R 775 /srv/
    touch /tmp/deployment-cleared
fi