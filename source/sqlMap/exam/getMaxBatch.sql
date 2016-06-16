select top 1 COUNT(*) as ncount from (
  select Studentno,CourseNo2 from TestStudent group by Studentno,CourseNo2
) c group by c.Studentno
ORDER BY ncount DESC