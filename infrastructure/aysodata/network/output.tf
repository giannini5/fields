output "public_subnet_id" {
  value = aws_subnet.public.*.id
}

output "aysodata_server_open_security_group_id" {
  value = aws_security_group.open_aysodata_server.id
}

output "aysodata_server_http_port" {
  value = var.server_port
}

output "vpc_id" {
  value = aws_vpc.main.id
}

output "aysodata_aws_key_name" {
  description = "aysodata AWS Key Name"
  value       = aws_key_pair.aysodata.key_name
}

# Region122 Output
output "region122_elb_dns_name" {
  value = aws_elb.region122_elb.dns_name
}

output "region122_route53_subdomain" {
  value = aws_route53_record.region122_route53_alias.fqdn
}

output "region122_elb_id" {
  value = aws_elb.region122_elb.id
}
