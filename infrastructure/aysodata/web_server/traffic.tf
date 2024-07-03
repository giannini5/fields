# aysodata load balancer settings
resource "aws_elb_attachment" "aysodata_elb_instances" {
  count    = length(aws_instance.aysodata)
  elb      = data.terraform_remote_state.network.outputs.region122_elb_id
  instance = aws_instance.aysodata[count.index].id
}
