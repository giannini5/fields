provider "aws" {
  region = "us-east-2"
}

data "aws_availability_zones" "all" {}

# Save Terraform state remotely in S3, with concurrency lock in dynamo
# see https:#medium.com/@jessgreb01/how-to-terraform-locking-state-in-s3-2dc9a5665cb6
terraform {
  backend "s3" {
    # All projects should use the same `bucket` and `dynamodb_table`
    bucket         = "giannini5.terraform-remote-state-storage"
    dynamodb_table = "giannini5.terraform-remote-state-root-lock"

    # IMPORTANT: `key` should match the git project structure, and must be unique for all projects
    key = "aysodata/storage/terraform.tfstate"

    # IMPORTANT: region should always be us-east-2, independent of project resources
    region = "us-east-2"
  }
}
