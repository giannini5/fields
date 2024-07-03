resource "aws_instance" "aysodata" {
  count                  = local.aysodata_count
  ami                    = "ami-0e206624f68a3d9db"
  instance_type          = local.instance_type
  subnet_id              = data.terraform_remote_state.network.outputs.public_subnet_id[0]
  vpc_security_group_ids = [data.terraform_remote_state.network.outputs.aysodata_server_open_security_group_id]
  key_name               = data.terraform_remote_state.network.outputs.aysodata_aws_key_name

  root_block_device {
    volume_size = 16
  }

  tags = {
    Name = format("%s-%02d", local.name, count.index + 1)
  }
}
