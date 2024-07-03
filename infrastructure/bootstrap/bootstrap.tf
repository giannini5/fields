#
# terraform remote state file setup
# https://medium.com/@jessgreb01/how-to-terraform-locking-state-in-s3-2dc9a5665cb6
#

# create an S3 bucket to store the state file in
resource "aws_s3_bucket" "terraform-state-storage-s3" {
    bucket = "giannini5.terraform-remote-state-storage"

    lifecycle {
      prevent_destroy = false
    }
    force_destroy = true

    tags = {
      Name          = "giannini5.terraform-infrastructure-bucket"
      Environment   = "Global"
      Owner         = "giannini5"
      Automation    = "terraform"
      Service       = "giannini5"
    }
}

# create a dynamodb table for locking the state file
resource "aws_dynamodb_table" "dynamodb-terraform-state-lock" {
  name              = "giannini5.terraform-remote-state-root-lock"
  hash_key          = "LockID"
  read_capacity     = 20
  write_capacity    = 20

  attribute {
    name = "LockID"
    type = "S"
  }

  tags = {
    Name          = "giannini5.terraform-infrastructure-dynamodb"
    Environment   = "Global"
    Owner         = "giannini5"
    Automation    = "terraform"
    Service       = "giannini5"
  }
}
