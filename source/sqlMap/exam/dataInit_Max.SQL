select top 1 count(Studentno) as row from TestStudent group By StudentNo
order by row desc