select * from (SELECT row_number() over(order by 编号)as row,编号 as bianhao,名称 as name,CONVERT(varchar(10),制定日期,20) as time,制定学院 as school,计划类别 as programtype
FROM VIEWPROGRAMS
WHERE SCHOOL=:SCHOOL)as b where b.row between :start and :end
