data "terraform_remote_state" "network" {
  backend = "s3"

  # Needs to be prod or qa
  workspace = local.env

  config = {
    bucket = "giannini5.terraform-remote-state-storage"
    key    = "aysodata/network/terraform.tfstate"
    region = "us-east-2"
  }
}
