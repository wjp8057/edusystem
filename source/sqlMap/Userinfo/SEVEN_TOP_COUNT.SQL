SELECT count(*) as ROWS
from 教学质量评估成绩 inner join users on users.teacherno=教学质量评估成绩.教师号
where users.username=:username