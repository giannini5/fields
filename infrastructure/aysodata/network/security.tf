// A Security group to attach to an EC2 instance accessible from any IP
resource "aws_security_group" "open_aysodata_server" {
  name   = format("%s-open-instance", local.name)
  vpc_id = aws_vpc.main.id

  tags = {
    Name = format("%s-open-server", local.name)
  }
}

// Even in open instances, restrict SSH access.
resource "aws_security_group_rule" "open_aysodata_server_ssh" {
  type              = "ingress"
  from_port         = 22
  to_port           = 22
  protocol          = "tcp"
  cidr_blocks       = var.allowed_ips
  security_group_id = aws_security_group.open_aysodata_server.id
}

resource "aws_security_group_rule" "open_aysodata_server_http" {
  type              = "ingress"
  from_port         = var.server_port
  to_port           = var.server_port
  protocol          = "tcp"
  cidr_blocks       = var.allowed_ips
  security_group_id = aws_security_group.open_aysodata_server.id
}

resource "aws_security_group_rule" "open_aysodata_server_ingress" {
  type                     = "ingress"
  from_port                = var.server_port
  to_port                  = var.server_port
  protocol                 = "tcp"
  security_group_id        = aws_security_group.open_aysodata_server.id
  source_security_group_id = aws_security_group.open_aysodata_lb.id
}

resource "aws_security_group_rule" "open_aysodata_server_egress" {
  type              = "egress"
  from_port         = 0
  to_port           = 0
  protocol          = "-1"
  cidr_blocks       = var.all_ips
  security_group_id = aws_security_group.open_aysodata_server.id
}
