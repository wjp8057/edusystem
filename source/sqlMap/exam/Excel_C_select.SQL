select
COURSENO as kh,
ROOMNO1 as kc1,
seats1 as kw1,
rs1 as rs1,
ROOMNO2 as kc2,
seats2 as kw2,
rs2 as rs2,
ROOMNO3 as kc3,
seats3 as kw3,
rs3 as rs3,
ATTENDENTS as xkrs,
监考教师1+','+监考教师2+','+监考教师3 as kc1jk,
监考教师4+','+监考教师5+','+监考教师6 as kc2jk,
监考教师7+','+监考教师8+','+监考教师9 as kc3jk,
rem
from TESTPLAN
where year=:year and term=:term and examType='C' and COURSENO like :COURSENO