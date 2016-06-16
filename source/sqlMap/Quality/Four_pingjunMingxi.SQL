SELECT cast(Avg(one*1.0) as decimal(18,2)) [one] ,
      cast (avg([two]*1.0) as decimal(18,2)) [two]
      , cast (avg([three]*1.0)  as decimal(18,2))  [three]
      , cast (avg([four]*1.0) as decimal(18,2))  [four]
      , cast (avg([five]*1.0) as decimal(18,2))  [five]
      , cast (avg([six]*1.0)  as decimal(18,2)) [six]
      , cast (avg([seven]*1.0) as decimal(18,2))  [seven]
      , cast (avg([eight]*1.0)  as decimal(18,2)) [eight]
      , cast (avg([nine]*1.0) as decimal(18,2))  [nine]
      , cast (avg([ten]*1.0) as decimal(18,2))  [ten]
      , cast (avg([total]*1.0) as decimal(18,2))   [zongfen]
  FROM  CourseQualityDetail
  where map=:recno