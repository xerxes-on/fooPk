#!/bin/bash
set -e

# ---
# Copy .env file for frontend servers (ASG):
if [ "$DEPLOYMENT_GROUP_NAME" == "prod-foodpunk" ];
then
    cp -f /mnt/efs/shared_folders/envs/meinplan/.env /srv/foodpunk_laravel_v2/.env || exit 2
    cp -f /mnt/efs/shared_folders/envs/meinplan/firebase-pushnotifications-credentials.json /srv/foodpunk_laravel_v2/firebase-pushnotifications-credentials.json || exit 21
fi

# ---
# Copy .env file for Static server:
if [ "$DEPLOYMENT_GROUP_NAME" == "prod-foodpunk-static" ];
then
    cp -f /mnt/efs/shared_folders/envs/static/.env /srv/foodpunk_laravel_v2/.env || exit 3
    cp -f /mnt/efs/shared_folders/envs/static/firebase-pushnotifications-credentials.json /srv/foodpunk_laravel_v2/firebase-pushnotifications-credentials.json || exit 31
fi

# ---
# Copy .env file for Staging server:
if [ "$DEPLOYMENT_GROUP_NAME" == "staging-foodpunk" ];
then
    # cp -f /mnt/efs/shared_folders/envs/staging/.env /srv/foodpunk_laravel_v2/.env || exit 4
    cp -f /srv/.env /srv/foodpunk_laravel_v2/.env || exit 40
    cp -f /srv/firebase-pushnotifications-credentials.json /srv/foodpunk_laravel_v2/firebase-pushnotifications-credentials.json || exit 41
fi

# ---
# Get EC2 Instance ID or $HOSTNAME var value or i-RNDXXXXX
#
EC2_INSTANCE_ID=$(curl -s -m 3 http://169.254.169.254/latest/meta-data/instance-id)
CURL_EXIT_CODE=$?
if [[ $CURL_EXIT_CODE -gt 0 ]];
then
    if [[ $CURL_EXIT_CODE -eq 28 ]];
    then
        echo "curl connection timeout error!"
    else
        echo "curl error: $CURL_EXIT_CODE"
   fi
fi

if [[ -z ${EC2_INSTANCE_ID} ]];
then
    echo "Something went wrong and variable EC2_INSTANCE_ID is not set."
    if [[ -z ${HOSTNAME} ]];
    then
        EC2_INSTANCE_ID=${HOSTNAME}
        echo "${EC2_INSTANCE_ID}"
    else
        echo "Generate random EC2 Instance ID ..."
        EC2_INSTANCE_ID=i-RND$(openssl rand -hex 5)
        echo "Random EC2 Instance ID: ${EC2_INSTANCE_ID}"
        echo "${EC2_INSTANCE_ID}" > /srv/deployment_rnd_instanc_id
    fi
else
    echo "Variable EC2_INSTANCE_ID is already set (metadata). EC2 Instance ID: ${EC2_INSTANCE_ID}"
fi

# Log settings
## Inject EC2 Instance name/ID to app .env file
cd /srv/foodpunk_laravel_v2/ || exit 5
sed -i'-sed-bkp' "s/^LOG_INSTANCE_ID=.*/LOG_INSTANCE_ID=${EC2_INSTANCE_ID}/g" .env
## Insert BUNDLE_ETAG/git commit SHA256 hash
# RELEASE_COMMIT=$(aws deploy get-deployment --deployment-id ${DEPLOYMENT_ID}  --query "deploymentInfo.revision.s3Location.key" --output text)
# RELEASE_COMMIT=${RELEASE_COMMIT#packages/}
sed -i'-sed-bkp' "s/^LOG_RELEASE_COMMIT=.*/LOG_RELEASE_COMMIT=${DEPLOYMENT_ID}/g" .env
## Insert release/deployment date
RELEASE_DATE=$(date +%Y%m%d_%H%m)
sed -i'-sed-bkp' "s/^LOG_RELEASE_DATE=.*/LOG_RELEASE_DATE=${RELEASE_DATE}/g" .env

#sudo apt install php8.1-pdo-mysql -y
sudo chown www-data:www-data /srv/foodpunk_laravel_v2/.env

# ---
# Settings for Static and Front servers
if [ "$DEPLOYMENT_GROUP_NAME" == "prod-foodpunk-static" ] || [ "$DEPLOYMENT_GROUP_NAME" == "prod-foodpunk" ];
then
    # ---
    # Prepare folders and symlinks
    sudo mkdir -p /srv/foodpunk_laravel_v2/storage
    ln -s /mnt/efs/shared_folders/public/uploads/ /srv/foodpunk_laravel_v2/public/
    ln -s /mnt/efs/shared_folders/public/pdf_files/ /srv/foodpunk_laravel_v2/public/
    ln -s /mnt/efs/shared_folders/storage/app/public/ /srv/foodpunk_laravel_v2/public/storage
    ln -s /mnt/efs/shared_folders/storage/app/ /srv/foodpunk_laravel_v2/storage/
    ln -s /mnt/efs/shared_folders/storage/logs/ /srv/foodpunk_laravel_v2/storage/
    sudo mkdir -p /srv/foodpunk_laravel_v2/bootstrap/cache
    sudo chown -R www-data:www-data /srv/foodpunk_laravel_v2/bootstrap/cache
    # sudo chmod 764 /srv/foodpunk_laravel_v2/public/ -R ???? TODO::review possible executable files in user's upload folders

    sudo mkdir -p /srv/foodpunk_laravel_v2/storage/logs/artisan/
    sudo mkdir -p /srv/foodpunk_laravel_v2/storage/framework/cache
    sudo mkdir -p /srv/foodpunk_laravel_v2/storage/framework/cache/data
    sudo mkdir -p /srv/foodpunk_laravel_v2/storage/framework/sessions
    sudo mkdir -p /srv/foodpunk_laravel_v2/storage/framework/testing
    sudo mkdir -p /srv/foodpunk_laravel_v2/storage/framework/views

    cd /srv/foodpunk_laravel_v2/  || exit 6
    sudo chown -R www-data:www-data /srv/
    sudo chmod -R 775 /srv/

    # ---
    # Check NodeJS version
    CURRENT_NODEJS_VERSION="$(node --version)"
    REQUIRED_NODEJS_VERSION="v18.2.0"
    if [ "$(printf '%s\n' "$CURRENT_NODEJS_VERSION" "$REQUIRED_NODEJS_VERSION" | sort -V | head -n1)" = "$REQUIRED_NODEJS_VERSION" ]; then
            echo "NodeJS version [${CURRENT_NODEJS_VERSION}] is greater than or equal to '${REQUIRED_NODEJS_VERSION}'"
    else
            echo "NodeJS version [${CURRENT_NODEJS_VERSION}] is less than ${REQUIRED_NODEJS_VERSION}"
            exit 8
    fi

    # ---
    # NPM install
    npm install

    # Building assets minified
    npm run production

    # ---
    # Install composer
    sudo -Hu www-data composer install

    # ---
    # Composer post update
    sudo -Hu www-data composer run-script post-update-cmd
    #

    # ---
    # Configure
    sudo -Hu www-data php artisan config:cache
    yes | php artisan sleepingowl:install
    sudo -Hu www-data php artisan cache:clear
    sudo -Hu www-data php artisan config:cache
    sudo -Hu www-data php artisan view:cache
    sudo -Hu www-data php artisan optimize:clear

    #run migrations only on static server
    if [ "$DEPLOYMENT_GROUP_NAME" == "prod-foodpunk-static" ];
    then
          sudo -Hu www-data php /srv/foodpunk_laravel_v2/artisan migrate --force
    fi

    # ---
    # Restart services
    sudo systemctl restart php8.2-fpm.service
    sudo systemctl restart nginx.service
    sudo chown -R www-data:www-data /srv/
    sudo chmod 775 -R /srv/
    touch /tmp/deployment-done
fi

# ---
# Static server specific settings
if [ "$DEPLOYMENT_GROUP_NAME" == "prod-foodpunk-static" ];
then
    sudo -Hu www-data php artisan queue:restart
    sudo supervisorctl reread
    sudo supervisorctl reload
    # sleep 5
    # sudo supervisorctl restart all
fi
# mayeb we need swagger on static too?
if [ "$DEPLOYMENT_GROUP_NAME" == "prod-foodpunk" ];
then
     mkdir -p /srv/foodpunk_laravel_v2/storage/api-docs/
     sudo chown -R www-data:www-data /srv/foodpunk_laravel_v2/storage/api-docs/
     sudo chmod 775 /srv/foodpunk_laravel_v2/storage/api-docs/ -R
     sudo -Hu www-data php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
     sudo -Hu www-data php artisan l5-swagger:generate --all
     # temporary copy because  l5-swagger:generate not geneate proper data TODO:: Pavel please create correct notes
     cp -f /mnt/efs/shared_folders/storage/api-docs/api-docs.json /srv/foodpunk_laravel_v2/storage/api-docs/api-docs.json
     sudo -Hu www-data php artisan optimize:clear

fi

#
# Settings for staging environment
#
if [ "$DEPLOYMENT_GROUP_NAME" == "staging-foodpunk" ];
then
    cd /srv/foodpunk_laravel_v2/ || exit 7
    sudo mkdir -p /srv/foodpunk_laravel_v2/bootstrap/cache
    sudo mkdir -p /srv/foodpunk_laravel_v2/storage/logs/artisan/
    sudo mkdir -p /srv/foodpunk_laravel_v2/storage/framework/cache
    sudo mkdir -p /srv/foodpunk_laravel_v2/storage/framework/cache/data
    sudo mkdir -p /srv/foodpunk_laravel_v2/storage/framework/sessions
    sudo mkdir -p /srv/foodpunk_laravel_v2/storage/framework/testing
    sudo mkdir -p /srv/foodpunk_laravel_v2/storage/framework/views
    # sudo mkdir -p /srv/foodpunk_laravel_v2/public/storage

    # ---
    # Create folder if not exists
    if [ -d "/srv/storage/app/public/" ];
    then
        echo "'/srv/storage/app/public/' directory exists"
    else
	    echo "'/srv/storage/app/public/' directory does not exist"
        sudo mkdir -p /srv/storage/app/public/
    fi

    # ---
    # Create folder if not exists
    if [ -d "/srv/pdf_files/" ];
    then
        echo "'/srv/pdf_files/' directory exists"
    else
	    echo "'/srv/pdf_files/' directory does not exist"
        sudo mkdir -p /srv/pdf_files/
    fi

    sudo ln -s /srv/public/uploads/ /srv/foodpunk_laravel_v2/public/
    sudo ln -s /srv/pdf_files/ /srv/foodpunk_laravel_v2/public/
    sudo ln -s /srv/storage/app/public/ /srv/foodpunk_laravel_v2/public/storage
    sudo ln -s /srv/storage/app/ /srv/foodpunk_laravel_v2/storage/
    sudo ln -s /srv/storage/api-docs/ /srv/foodpunk_laravel_v2/storage/
    sudo chown -R www-data:www-data /srv/foodpunk_laravel_v2/
    sudo chmod -R 775 /srv/foodpunk_laravel_v2/
    # ---
    # Check NodeJS version
    CURRENT_NODEJS_VERSION="$(node --version)"
    REQUIRED_NODEJS_VERSION="v18.2.0"
    if [ "$(printf '%s\n' "$CURRENT_NODEJS_VERSION" "$REQUIRED_NODEJS_VERSION" | sort -V | head -n1)" = "$REQUIRED_NODEJS_VERSION" ]; then
            echo "NodeJS version [${CURRENT_NODEJS_VERSION}] is greater than or equal to '${REQUIRED_NODEJS_VERSION}'"
    else
            echo "NodeJS version [${CURRENT_NODEJS_VERSION}] is less than ${REQUIRED_NODEJS_VERSION}"
            exit 9
    fi
    # ---
    # NPM install
    npm install
    # Building assets minified
    npm run production
    # ---
    # Install composer
    sudo -Hu www-data composer install
    # ---
    # Composer post update
    sudo -Hu www-data composer run-script post-update-cmd
    #
    sudo -Hu www-data php artisan migrate
    sudo -Hu www-data php artisan cache:clear
    sudo -Hu www-data php artisan config:cache
    sudo -Hu www-data php artisan view:cache
    sudo -Hu www-data php artisan optimize:clear
    sudo systemctl restart nginx.service
    sudo chown -R www-data:www-data /srv/foodpunk_laravel_v2/
    sudo chmod 775 -R /srv/foodpunk_laravel_v2/

    sudo -Hu www-data php artisan queue:restart
    sudo supervisorctl reread
    sudo supervisorctl reload
fi
