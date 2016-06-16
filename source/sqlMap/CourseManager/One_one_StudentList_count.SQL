select count(*) as ROWS from(select row_number() over(order by 冲突 desc,makingcredit.coursecredit+makingcredit.addcredit desc,isnull(学期公选课门数,0) desc,temp1.学号
) as row,TEMP1.学号 as xh,
temp1.姓名 as xm,
sexcode.name as xb,
学生班级 as xsbj,
班号 as bh,
总学分 as zxf,
选课门数 as xkms,
修课计划 as xkjh,
冲突 as ct,
重修 as cx,
修课方式 as xkfs,
考核 as kh,
听课方式 as tkfs,
重修费 as cxf,
makingcredit.coursecredit as hdgxxf,
makingcredit.addcredit as cxxf,
isnull(学期公选课学分,0) xqgxkxf,
isnull(学期公选课门数,0) xqgxkms
from (SELECT 学号,姓名,学生班级,班号,总学分,选课门数,修课计划 AS 修课计划 ,冲突,CAST(重修 AS INT) AS 重修,修课方式,考核,听课方式,重修费
FROM VIEWCOURSESELECTIONSTUDENTS
WHERE YEAR= :YONE
AND TERM= :TTWO
AND COURSENOGROUP =:COURSENO
) as temp1 left outer join makingcredit on makingcredit.studentno=temp1.学号
left outer join (select sum(courses.credits) 学期公选课学分,count(*) as 学期公选课门数,studentno as 学号
from R32 inner join courses on courses.courseno=R32.courseno
where (r32.courseno like '08%' or r32.[group] like 'G_' or r32.courseno like '007%') and year=:YTWO AND TERM=:TTWO
group by studentno) as temp3 on temp1.学号=temp3.学号 inner join students on students.studentno=temp1.学号
inner join sexcode on sexcode.code=students.sex
) as b