select count(*) as ROWS
from R7,Subsidiaries,Schools,Programs
where Subsidiaries.ClassNo=R7.ClassNo 
and R7.ProgramNo=:programno
and Subsidiaries.School=Schools.School
and R7.ProgramNo=Programs.ProgramNo
and R7.ClassNo=Subsidiaries.ClassNo
 
 
 
