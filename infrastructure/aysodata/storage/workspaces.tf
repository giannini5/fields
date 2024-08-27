# See https://www.terraform.io/docs/state/workspaces.html
# See https://stackoverflow.com/a/49621326/4492245
# See https://danielschaaff.com/2016/12/01/terraform-ami-maps/

#  This workspace map allows the creation of clones in different regions/vpc's without needing to use tfvar files
variable "workspace_guard" {
  type        = list(string)
  default     = ["prod"]
  description = "A simple protection that triggers an error to prevent accidently running `terraform apply` in a workspace outside of the desired list."
}

locals {
  workspace_guard = index(var.workspace_guard, terraform.workspace)
}
