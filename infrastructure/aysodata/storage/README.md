# Deploy and configure S3 bucket for aysodata

## Files
| File Name        | Description |
| ------------- |-------------|
| main.tf         | remote state, region and avaiability zone configuration |
| remote_state.tf | Access to network output data |
| workspace.tf    | variable overrides based on workspace |
| output.tf       | remote state output displayed after run and variable export that is usable by other directories |

## Quick Start
Workspace environment setup (replace `<workspace>` with prod, qa or dev)

1. Be on a whitelisted IP address - see network workspace.tf
2. Download `aysodata_rsa.pem` and place it in your `~/.ssh` directory.
3. Make sure that these four variables show up when you run `env | grep AWS`:
* AWS_ACCESS_KEY_ID
* AWS_SECRET_ACCESS_KEY
* TF_VAR_AWS_ACCESS_KEY_ID
* TF_VAR_AWS_SECRET_ACCESS_KEY
If these do not exist, you will need to put them in your `~/.zshrc` or equivalent shell profile.

```bash
$ terraform init
$ terraform workspace select <workspace>
```
