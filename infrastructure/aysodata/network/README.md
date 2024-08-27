# Deploy and configure network
## Prerequsites
1. bootstrap (should already be in place)

## Files
| File Name        | Description |
| ------------- |-------------|
| main.tf | remote state, region and availability zone configuration |
| network.tf | VPC, subnet deploy and configuration |
| security.tf | Network security group deploy and configuration |
| load_balancer.tf | Load balancer deploy and configuration |
| key_pair.tf | Key pair used for ssh access to EC2 instances |
| route_53.tf | Route 53 alias for domain to load balancer |
| variables.tf | variable definitions and assignment based on workspace |
| workspace.tf | variable overrides based on workspace |
| output.tf | remote state output displayed after run and variable export that is usable by other directories |

## Quick Start
1. For prod environment
```bash
$ terraform init
$ terraform workspace select prod
$ terraform apply
# Problems with terraform apply?  Try this and then run the ap:
$ export GODEBUG=asyncpreemptoff=1
```
More info here: https://stackoverflow.com/questions/70007818/terraform-the-plugin-encountered-an-error-and-failed-to-respond-to-the-plugin
