-- 
-- End of Day Game slots for Girsh
--
use schedule;
set @seasonName = "2022 - League";
set @startDay   = '2022-11-05';
set @endDay     = '2022-11-12';

select
    date_format(d.day, "%c/%e/%Y") as Date,
    "18:30" as startTime,
    "18:45" as endTime,
    l.thirdPartyFieldId,
    '122-U5G-50' as homeTeam,
    '122-U5G-51' as visitingTeam
from
    facility as f
    join season as s on
        s.id = f.seasonId
        and s.name = @seasonName
    join field as l on
        l.facilityId = f.id
    join gameTime as t on
        t.fieldId = l.id
    join gameDate as d on
        d.id = t.gameDateId
        and d.day >= @startDay
        and d.day <= @endDay
where
    f.name like "%UCSB%"
    or f.name like "%Girsh%"
group by 1, 2, 3, 4, 5, 6
into outfile "/Users/dag/Desktop/ayso/2022/vat_lastGameOfDay.txt" LINES TERMINATED BY '\n';
