data "terraform_remote_state" "aysodata_network" {
  backend = "s3"

  # See workspace for supported values
  workspace = local.env

  config = {
    bucket = "giannini5.terraform-remote-state-storage"
    key    = "aysodata/network/terraform.tfstate"
    region = "us-east-2"
  }
}
