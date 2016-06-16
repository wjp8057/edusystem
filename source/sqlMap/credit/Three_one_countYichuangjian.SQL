select count(*) as ROWS
from Creditbatchproject where (credittype=:cone or credittype=:ctwo) and projectname like :projectname AND projectstatus=1