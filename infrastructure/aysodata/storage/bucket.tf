resource "aws_s3_bucket" "aysodata_s3" {
  bucket = format("tf-%s-aysodata-s3", terraform.workspace)

  force_destroy = (replace(terraform.workspace, "prod", "") != terraform.workspace) ? true : false

  tags = {
    Name        = format("tf-%s-aysodata-bucket", terraform.workspace)
    Environment = terraform.workspace
  }
}

resource "aws_s3_bucket_public_access_block" "block_acls" {
  bucket             = aws_s3_bucket.aysodata_s3.id
  ignore_public_acls = true
}
