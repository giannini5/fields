resource "aws_route53_record" "region122_route53_alias" {
  zone_id = var.zone_id
  name    = local.region122_domain_name_prefix
  type    = "A"

  alias {
    name                   = aws_elb.region122_elb.dns_name
    zone_id                = aws_elb.region122_elb.zone_id
    evaluate_target_health = true
  }
}
