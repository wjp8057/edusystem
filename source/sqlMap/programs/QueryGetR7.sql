select Subsidiaries.ClassName As ClassName,
Subsidiaries.School As School,
R7.ProgramNo As ProgramNo,
R7.ClassNo As ClassNo ,
Programs.ProgName As ProgramName,
Schools.Name As SchoolName
from R7,Subsidiaries,Schools,Programs
where Subsidiaries.ClassNo=R7.ClassNo 
and R7.ProgramNo=:programno
and Subsidiaries.School=Schools.School
and R7.ProgramNo=Programs.ProgramNo
and R7.ClassNo=Subsidiaries.ClassNo
 
 
 
