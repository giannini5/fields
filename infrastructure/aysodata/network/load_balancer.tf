// A security group to attach to a Load Balancer that allows
// access from all IP addresses.
resource "aws_security_group" "open_aysodata_lb" {
  name_prefix = format("%s-open-aysodata-lb-", local.name)
  vpc_id      = aws_vpc.main.id

  tags = {
    Name = format("%s-open-aysodata-lb", local.name)
  }
}

resource "aws_security_group_rule" "open_aysodata_lb_http" {
  type              = "ingress"
  from_port         = 80
  to_port           = 80
  protocol          = "tcp"
  cidr_blocks       = var.all_ips
  security_group_id = aws_security_group.open_aysodata_lb.id
}

resource "aws_security_group_rule" "open_aysodata_lb_https" {
  type              = "ingress"
  from_port         = 443
  to_port           = 443
  protocol          = "tcp"
  cidr_blocks       = var.all_ips
  security_group_id = aws_security_group.open_aysodata_lb.id
}

resource "aws_security_group_rule" "open_aysodata_lb_aysodata_server" {
  type                     = "egress"
  from_port                = var.server_port
  to_port                  = var.server_port
  protocol                 = "tcp"
  source_security_group_id = aws_security_group.open_aysodata_server.id
  security_group_id        = aws_security_group.open_aysodata_lb.id
}

resource "aws_elb" "region122_elb" {
  name            = format("tf-%s-region122-elb", terraform.workspace)
  security_groups = [aws_security_group.open_aysodata_lb.id]
  subnets         = aws_subnet.public.*.id

  health_check {
    healthy_threshold   = 2
    unhealthy_threshold = 2
    timeout             = 3
    interval            = 30
    target              = format("HTTP:%d/", var.server_port)
  }

  listener {
    lb_port            = 443
    lb_protocol        = "https"
    instance_port      = var.server_port
    instance_protocol  = "http"
    ssl_certificate_id = var.ssl_certificate_id
  }
}
