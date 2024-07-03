locals {
  env = lookup(var.workspaces, "${terraform.workspace}.environment", "prod")
}

locals {
  name              = format("tf-%s-aysodata", terraform.workspace)
  aysodata_count    = 1
  instance_type     = lookup(var.workspaces, "${terraform.workspace}.instance_type", "t3.micro")
  source_dir        = "/usr/local/src"
}

variable "AWS_ACCESS_KEY_ID" {
  description = "AWS access key id"
}

variable "AWS_SECRET_ACCESS_KEY" {
  description = "AWS secret access key"
}
