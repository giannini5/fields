resource "aws_vpc" "main" {
  cidr_block           = var.cidr_block
  enable_dns_support   = true
  enable_dns_hostnames = true

  tags = {
    Name = local.name
  }
}

resource "aws_subnet" "public" {
  count                   = length(data.aws_availability_zones.all.names)
  vpc_id                  = aws_vpc.main.id
  availability_zone       = data.aws_availability_zones.all.names[count.index]
  cidr_block              = cidrsubnet(var.cidr_block, 8, count.index * 2)
  map_public_ip_on_launch = true

  tags = {
    Name = format("%s-public-%02d", local.name, count.index + 1)
  }

  lifecycle {
    create_before_destroy = true
  }
}

resource "aws_internet_gateway" "main" {
  vpc_id = aws_vpc.main.id

  tags = {
    Name = local.name
  }
}

resource "aws_route_table" "public" {
  vpc_id = aws_vpc.main.id

  tags = {
    Name = format("%s-public-00", local.name)
  }
}

resource "aws_route" "igw" {
  route_table_id         = aws_route_table.public.id
  gateway_id             = aws_internet_gateway.main.id
  destination_cidr_block = "0.0.0.0/0"
}

resource "aws_route_table_association" "public" {
  subnet_id      = aws_subnet.public.*.id[count.index]
  route_table_id = aws_route_table.public.id
  count          = length(data.aws_availability_zones.all.names)
}
