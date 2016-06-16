select b.studentno,b.name,b.classname,count(*) num,CAST(sum(b.dis_price) as decimal(38, 2)) price from studentbook b
where b.[year]=:year and b.term=:term and b.studentno like :studentno and b.classno like :classno and b.school like :school
group by b.studentno,b.name,b.classname order by b.studentno