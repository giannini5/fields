# See https://www.terraform.io/docs/state/workspaces.html
# See https://stackoverflow.com/a/49621326/4492245
# See https://danielschaaff.wordpress.com/2016/12/01/terraform-ami-maps/

variable "workspaces" {
  type = map(string)

  default = {
    # see variables.tf for workspace defaults

    # override the default variables by workspace
    "prod.environment"   = "prod"
    "prod.instance_type" = "t3.small"
  }
}

variable "workspace_guard" {
  type        = list(string)
  default     = ["prod"]
  description = "A simple protection that triggers an error to prevent accidentally running `terraform apply` in a workspace outside of the desired list."
}

locals {
  workspace_guard = index(var.workspace_guard, terraform.workspace)
}
