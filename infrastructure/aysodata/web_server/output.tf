output "public_ip" {
  value = aws_instance.aysodata.*.public_ip
}

output "aysodata_count" {
  value = length(aws_instance.aysodata)
}

output "aysodata_id" {
  value = aws_instance.aysodata.*.id
}
