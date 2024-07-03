variable "server_port" {
  description = "The port the server will use for HTTP requests"
  default     = 8080
}

variable "cidr_block" {
  default = "10.0.0.0/16"
}

variable "allowed_ips" {
  default = [
    "216.194.106.31/32",  # Dave's Office
    "149.20.194.135/32",  # Dave's Office
  ]
}

variable "all_ips" {
  default = ["0.0.0.0/0"]
}

variable "zone_id" {
  type        = string
  description = "aysodata.com Route 53 Zone ID"
  default     = "Z095688538WCMWV1165JU"
}

variable "ssl_certificate_id" {
  type        = string
  description = "The SSL certificate ARN for *.aysodata.com"
  default     = "arn:aws:acm:us-east-2:590184053379:certificate/673547cc-efbd-4f43-934b-a848fcdff4ca"
}

locals {
  env = lookup(var.workspaces, "${terraform.workspace}.environment", terraform.workspace)
}

locals {
  name                          = format("tf-%s-aysodata", local.env)
  region122_domain_name_prefix  = lookup(var.workspaces, "${terraform.workspace}.region122_domain_name_prefix", "region122")
}
