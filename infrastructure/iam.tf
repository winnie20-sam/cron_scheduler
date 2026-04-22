# ─────────────────────────────────────────────
# IAM Role for ECS Task — least privilege
# No wildcard permissions anywhere
# ─────────────────────────────────────────────

# Trust policy — only ECS tasks can assume this role
data "aws_iam_policy_document" "ecs_trust" {
  statement {
    actions = ["sts:AssumeRole"]
    principals {
      type        = "Service"
      identifiers = ["ecs-tasks.amazonaws.com"]
    }
  }
}

resource "aws_iam_role" "ecs_task" {
  name               = "${var.app_name}-ecs-task-role"
  assume_role_policy = data.aws_iam_policy_document.ecs_trust.json
}

# Permission policy — only allows reading the two secrets we defined
data "aws_iam_policy_document" "ecs_task_permissions" {
  statement {
    sid    = "ReadAppSecrets"
    effect = "Allow"

    actions = [
      "secretsmanager:GetSecretValue"   # no wildcards — only this one action
    ]

    resources = [
      aws_secretsmanager_secret.db_password.arn,
      aws_secretsmanager_secret.app_key.arn
    ]
  }
}

resource "aws_iam_policy" "ecs_task" {
  name   = "${var.app_name}-ecs-task-policy"
  policy = data.aws_iam_policy_document.ecs_task_permissions.json
}

resource "aws_iam_role_policy_attachment" "ecs_task" {
  role       = aws_iam_role.ecs_task.name
  policy_arn = aws_iam_policy.ecs_task.arn
}

# Execution role — allows ECS to pull image and write logs
resource "aws_iam_role" "ecs_execution" {
  name               = "${var.app_name}-ecs-execution-role"
  assume_role_policy = data.aws_iam_policy_document.ecs_trust.json
}

resource "aws_iam_role_policy_attachment" "ecs_execution" {
  role       = aws_iam_role.ecs_execution.name
  policy_arn = "arn:aws:iam::aws:policy/service-role/AmazonECSTaskExecutionRolePolicy"
}
