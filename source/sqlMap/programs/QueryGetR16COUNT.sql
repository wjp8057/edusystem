select COUNT(R16.ClassNo) As ROWS
from R16,Classes,Programs,Schools
where R16.ClassNo=Classes.ClassNo and R16.ProgramNo =:programno
and R16.ProgramNo=Programs.ProgramNo
and Classes.School=Schools.School
 
 
