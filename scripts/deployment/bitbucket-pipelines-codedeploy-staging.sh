cp ./.env.example ./.env && \
aws configure set default.region "${AWS_REGION}" && \
aws configure set aws_access_key_id "${AWS_ACCESS_KEY_ID}" && \
aws configure set aws_secret_access_key "${AWS_SECRET_ACCESS_KEY}" && \
aws deploy push --application-name "${AWS_CODEDEPLOY_APP}" --s3-location s3://"${AWS_S3_BUCKET}"/packages/"${BITBUCKET_COMMIT}"-staging.zip --ignore-hidden-files && \
aws deploy create-deployment --application-name "${AWS_CODEDEPLOY_APP}" --deployment-config-name CodeDeployDefault.OneAtATime --s3-location bucket="${AWS_S3_BUCKET}",key=packages/"${BITBUCKET_COMMIT}"-staging.zip,bundleType=zip --deployment-group-name "${AWS_CODEDEPLOY_GROUP_STAGING}"
