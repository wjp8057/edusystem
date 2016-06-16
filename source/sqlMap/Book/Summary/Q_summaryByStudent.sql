select b.studentno,b.name,b.classname,CAST(sum(b.price) as decimal(38, 2)) price,CAST(sum(b.dis_price) as decimal(38, 2)) dis_price from studentbook b
where b.[year]=:year and b.term=:term and b.studentno like :studentno and b.name like :name and b.school like :school
group by b.studentno,b.name,b.classname order by b.studentno