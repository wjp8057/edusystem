select * from (SELECT row_number() over(order by YEAR) as row,YEAR as xn,SCHOOL xy,studentscore xpjf,schoolscore xxpf,collegescore xypf,totalscore zhpf,rank  dj,rem bz
from 教学质量评估总评 inner join users on users.teacherno=教学质量评估总评.teacherno
where users.username=:username)AS B WHERE B.row between :start and :end
