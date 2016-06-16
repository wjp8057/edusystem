select l.[课号] courseno,l.[课名] coursename,l.[学号] studentno,l.[姓名] name,case l.[性别] when 'F' then '女' else '男' end sex,
str(l.[学分],10,1) score,str(l.[周学时],10,1) z_hour,str(l.[实验学时],10,1) s_hour,str(l.[上机学时],10,1) sj_hour,str(l.[课时酬金],10,1) [sum] 
 from [l_view_旁听生选课名单] l where l.[学年]=:year and l.[学期]=:term and l.[学号] like :studentno