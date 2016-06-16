SELECT count(*) as ROWS
from 教学质量评估总评 inner join users on users.teacherno=教学质量评估总评.teacherno
where users.username=:username
