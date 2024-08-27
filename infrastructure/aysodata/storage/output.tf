output "s3_bucket_name" {
  value = aws_s3_bucket.aysodata_s3.bucket
}

output "s3_bucket_domain" {
  value = aws_s3_bucket.aysodata_s3.bucket_regional_domain_name
}
