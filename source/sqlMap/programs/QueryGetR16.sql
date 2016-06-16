select R16.ClassNo As ClassNo, Classes.ClassName As ClassName, Classes.School As School, Classes.Grade As Grade,
R16.ProgramNo As ProgramNo, Programs.ProgName As ProgramName, Schools.Name As SchoolName
from R16,Classes,Programs,Schools
where R16.ClassNo=Classes.ClassNo and R16.ProgramNo =:programno
and R16.ProgramNo=Programs.ProgramNo
and Classes.School=Schools.School