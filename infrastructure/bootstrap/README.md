# Bootstrapping all giannini5 software deployments on AWS
Run the Quick Start steps below one time and never again for your AWS account.

## Prerequsites
1. AWS Account w/ access key and secret key in environment variables
    - AWS_ACCESS_KEY_ID
    - AWS_SECRET_ACCESS_KEY
    - TF_VAR_AWS_ACCESS_KEY_ID
    - TF_VAR_AWS_SECRET_ACCESS_KEY
1. terraform version 1.6.3

## Quick Start
1. tfenv use 1.6.3
1. terraform init
2. terraform plan
    - Expect s3 bucket to be created
    - Expect dynamoDb table to be created
3. terraform apply
    - s3 bucket is created
    - dynamoDb table is created
4. Verify
    - Check by logging into AWS Console
