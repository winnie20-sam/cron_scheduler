
resource "aws_secretsmanager_secret" "db_password" {
  name        = "${var.app_name}/db-password"
  description = "Laravel database password"
}

resource "aws_secretsmanager_secret_version" "db_password" {
  secret_id     = aws_secretsmanager_secret.db_password.id
  secret_string = jsonencode({
    password = "CHANGE_ME_BEFORE_APPLY"   # you set the real value in AWS console
  })
}

resource "aws_secretsmanager_secret" "app_key" {
  name        = "${var.app_name}/app-key"
  description = "Laravel APP_KEY"
}

resource "aws_secretsmanager_secret_version" "app_key" {
  secret_id     = aws_secretsmanager_secret.app_key.id
  secret_string = jsonencode({
    key = "CHANGE_ME_BEFORE_APPLY"
  })
}
