-- 
-- Stand By Game Slots
--
use schedule;
set @seasonName     = "2021 - League";
set @startDate      = '2021-11-06';
set @endDate        = '2021-11-14';
set @homeTeamId     = 80;
set @visitingTeamId = 81;

select
    date_format(data.day, "%c/%e/%Y") as Date,
    left(ifnull(data.actualStartTime, data.startTime), 5) as Start,
    left(addtime(ifnull(data.actualStartTime, data.startTime),
            case when data.gameDurationMinutes = 60 then "01:00:00"
                 when data.gameDurationMinutes = 75 then "01:15:00"
                 when data.gameDurationMinutes = 90 then "01:30:00"
                 when data.gameDurationMinutes = 105 then "01:45:00"
                 else "error" end),
        5) as End,
    case when data.divisionName = '10U' and data.gender = 'Boys' then 961
         when data.divisionName = '10U' and data.gender = 'Girls' then 802
         when data.divisionName = '12U' and data.facilityName like '%Girsh%' then 1084
         when data.divisionName = '12U' and data.facilityName like '%Storke%' then 921
         when data.divisionName = '14U' and data.facilityName like '%Rec%' then 1001
         when data.divisionName = '14U' and data.facilityName like '%Storke%' then 1100
         else 'ERROR' end as thridPartyFieldId,
    case when data.divisionName = '10U' then
            concat('122-', case when data.gender = 'Boys' then 'B' else 'G' end, data.divisionName, "-", @homeTeamId)
         else
            concat('122-B', data.divisionName, "-", @homeTeamId)
         end as homeTeam,
    case when data.divisionName = '10U' then
            concat('122-', case when data.gender = 'Boys' then 'B' else 'G' end, data.divisionName, "-", @visitingTeamId)
         else
            concat('122-B', data.divisionName, "-", @visitingTeamId)
         end as visitingTeam
from (
    select
        d.day,
        t.actualStartTime,
        t.startTime,
        i.name as divisionName,
        i.gameDurationMinutes,
        i.gender,
        f.name as facilityName
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
            and d.day between @startDate and @endDate
        join game as g on
            g.id = t.gameId
        join flight as h on
            h.id = g.flightId
        join schedule as c on
            c.id = h.scheduleId
        join division as i on
            i.id = c.divisionId
            and i.scoringTracked = 1
    where
        i.name in ('14U', '12U', '10U')
        -- i.name = '14U' and f.name like '%Storke%'
        -- !(i.name = '14U' and f.name like '%Storke%')
    group by 1, 2, 3, 4, 5, 6, 7
    order by 1, 2
) as data
group by 1, 2, 3, 4, 5, 6
into outfile "/Users/dag/Desktop/ayso/2021/vat_standBy.txt" LINES TERMINATED BY '\n';
