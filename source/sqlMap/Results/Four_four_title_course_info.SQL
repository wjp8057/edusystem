select :cone as courseno,courses.coursename,schools.name as schoolname,:yone as year,(:ytwo)+1 as year2,:term as term,courses.school
from courses inner join schools on courses.school=schools.school
where courses.courseno=substring(:ctwo,1,7)