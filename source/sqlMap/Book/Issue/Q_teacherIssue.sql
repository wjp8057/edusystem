select t.teacherno,t.name,t.school,s.name schoolname,
num=(select count(*) from teacherbook b where b.teacherno=t.teacherno and b.year=:year and b.term=:term),
price=(select CAST(sum(b.dis_price) as decimal(38, 2)) price from teacherbook b where b.teacherno=t.teacherno and b.year=:p_year and b.term=:p_term)
from teachers t left join schools s on s.school=t.school where t.school like :school and t.teacherno like :teacherno and t.name like :name
order by t.teacherno desc