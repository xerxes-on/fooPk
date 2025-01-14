#!/bin/bash
set -eux -o pipefail

cp ./.env.example ./.env && \
aws configure set default.region "${AWS_REGION}" && \
aws configure set aws_access_key_id "${AWS_ACCESS_KEY_ID}" && \
aws configure set aws_secret_access_key "${AWS_SECRET_ACCESS_KEY}" && \
DEPLOYMENT_ID=$(aws deploy create-deployment --application-name "${AWS_CODEDEPLOY_APP}" --deployment-config-name CodeDeployDefault.OneAtATime --s3-location bucket="${AWS_S3_BUCKET}",key=packages/"${BITBUCKET_COMMIT}".zip,bundleType=zip --deployment-group-name "${AWS_CODEDEPLOY_GROUP}" --query 'deploymentId' --output text)
while [ "${DEPLOYMENT_STATUS_ATTEMPTS}" -gt 0 ]
do
  DEPLOYMENT_STATE=$(aws deploy get-deployment --deployment-id "${DEPLOYMENT_ID}" --query 'deploymentInfo.status' --output text)
  if [ "${DEPLOYMENT_STATE}" == "Succeeded" ]; then
    echo "The deployment (${DEPLOYMENT_ID}) of '${AWS_CODEDEPLOY_APP}' application is 'Succeeded'. Goodbye!"
    exit 0
    # break
  fi
  if [ "${DEPLOYMENT_STATE}" == "Failed" ]; then
    echo "The deployment (${DEPLOYMENT_ID}) is 'Failed'. Bad news!"
    exit 5
  fi
  echo "Let's wait for the STATUS ${DEPLOYMENT_STATUS_DELAY} seconds."
  DEPLOYMENT_STATUS_ATTEMPTS=$(( DEPLOYMENT_STATUS_ATTEMPTS - 1 ))
  sleep "${DEPLOYMENT_STATUS_DELAY}"
done
echo "The deployment (${DEPLOYMENT_ID}) is 'temed out'. Bad news: looks like something went wrong!"
exit 255
