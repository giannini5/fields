use schedule;
-- select * from facility;
-- select * from field where facilityId = 1;
--
-- Dump schedules for import into Web Youth Soccer
--    TODO: Add support for Standby game slot creation
--    TODO: Add support for End-Of-Day game slot creation so nets are not taken down
--
set @division = "14U";
set @startDate = "2017-11-10";
set @tempHomeTeam = "50";
set @tempVisitingTeam = "51";

select
    data.Date,
    data.Start,
    data.End,
    data.thirdPartyFieldId,
    concat('122-', data.division, "-", data.homeTeamNumber) as homeTeam,
    concat('122-', data.division, "-", data.visitingTeamNumber) as visitingTeam
from (
    select
        date_format(d.day, "%c/%e/%Y") as Date,
        left(ifnull(t.actualStartTime, t.startTime), 5) as Start,
        left(addtime(ifnull(t.actualStartTime, t.startTime),
                case when gameDurationMinutes = 60 then "01:00:00"
                     when gameDurationMinutes = 75 then "01:15:00"
                     when gameDurationMinutes = 90 then "01:30:00"
                     else "error" end),
            5) as End,
        f.thirdPartyFieldId,
        case when h.nameId is null then @tempHomeTeam 
             when right(h.nameId, 2) between "01" and "09" then right(h.nameId, 1)
             else right(h.nameId, 2) end as homeTeamNumber,
        case when v.nameId is null then @tempVisitingTeam
             when right(v.nameId, 2) between "01" and "09" then right(v.nameId, 1)
             else right(v.nameId, 2) end as visitingTeamNumber,
        concat("U", left(i.name, length(i.name) - 1),
            case when i.gender = 'Boys' then 'B' else 'G' end) as division,
        g.title
    from
        game as g
        join gameTime as t on
            t.id = g.gameTimeId
        join gameDate as d on
            d.id = t.gameDateId
            and d.day > @startDate
        left outer join team as h on
            h.id = g.homeTeamId
        left outer join team as v on
            v.id = g.visitingTeamId
        join flight as l on
            l.id = g.flightId
        join schedule as s on
            s.id = l.scheduleId
        join division as i on
            i.id = s.divisionId
            and i.name = @division
        join field as f on
            f.id = t.fieldId
    ) as data
into outfile "/Users/dag/ayso/schedule_VAT_14U.txt" LINES TERMINATED BY '\n';
