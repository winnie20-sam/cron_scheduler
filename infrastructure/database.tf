# ─────────────────────────────────────────────
# RDS MySQL 8.0 — in private subnet only
# ─────────────────────────────────────────────
resource "aws_db_subnet_group" "main" {
  name       = "${var.app_name}-db-subnet-group"
  subnet_ids = [aws_subnet.private_a.id, aws_subnet.private_b.id]

  tags = { Name = "${var.app_name}-db-subnet-group" }
}

resource "aws_security_group" "rds" {
  name   = "${var.app_name}-rds-sg"
  vpc_id = aws_vpc.main.id

  # Only accepts connections from ECS containers, nothing else
  ingress {
    from_port       = 3306
    to_port         = 3306
    protocol        = "tcp"
    security_groups = [aws_security_group.ecs.id]
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = { Name = "${var.app_name}-rds-sg" }
}

resource "aws_db_instance" "main" {
  identifier        = "${var.app_name}-db"
  engine            = "mysql"
  engine_version    = "8.0"
  instance_class    = "db.t3.micro"
  allocated_storage = 20

  db_name  = "health_check"
  username = var.db_username

  # Password pulled from Secrets Manager
  password = jsondecode(
    aws_secretsmanager_secret_version.db_password.secret_string
  )["password"]

  db_subnet_group_name   = aws_db_subnet_group.main.name
  vpc_security_group_ids = [aws_security_group.rds.id]

  skip_final_snapshot     = false
  final_snapshot_identifier = "${var.app_name}-final-snapshot"
  publicly_accessible     = false

  tags = { Name = "${var.app_name}-db" }
}
