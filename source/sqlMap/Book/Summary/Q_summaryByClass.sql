select b.classno,b.classname,s.name schoolname,CAST(sum(b.price) as decimal(38, 2)) price,CAST(sum(b.dis_price) as decimal(38, 2)) dis_price
from studentbook b left join schools s on b.school=s.school 
where year=:year and term=:term and b.classno like :classno and b.classname like :classname and b.school like :school
group by b.classno,b.classname,s.name order by b.classname