--
-- Dump schedules for import into Web Youth Soccer
--    TODO: Add support for Standby game slot creation
--
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
        left(t.startTime, 5) as Start,
        left(addtime(t.startTime,
                case when gameDurationMinutes = 60 then "01:00:00"
                     when gameDurationMinutes = 75 then "01:15:00"
                     when gameDurationMinutes = 90 then "01:30:00"
                     else "error" end),
            5) as End,
        f.thirdPartyFieldId,
        case when right(h.nameId, 2) between "01" and "09" then right(h.nameId, 1) else right(h.nameId, 2) end as homeTeamNumber,
        case when right(v.nameId, 2) between "01" and "09" then right(v.nameId, 1) else right(v.nameId, 2) end as visitingTeamNumber,
        concat("U", left(i.name, length(i.name) - 1),
            case when i.gender = 'Boys' then 'B' else 'G' end) as division
    from
        game as g
        join gameTime as t on
            t.id = g.gameTimeId
        join gameDate as d on
            d.id = t.gameDateId
        join team as h on
            h.id = g.homeTeamId
        join team as v on
            v.id = g.visitingTeamId
        join division as i on
            i.id = h.divisionId
            and i.name = "10U"
        join field as f on
            f.id = t.fieldId
    ) as data
into outfile "/Users/dag/ayso/schedule_3.txt" LINES TERMINATED BY '\n';
