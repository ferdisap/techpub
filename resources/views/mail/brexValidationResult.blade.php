<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
</head>
<body style="font-family:Verdana, Geneva, Tahoma, sans-serif">
  <div>Dear Mr. / Ms.</div>
  <div>
    <p>Validation by BREX has been conducted with result as follows:
      @foreach($results as $o)
        <div>
          Validator: <span>{{ $o['validator'] }}</span>
          Validatee: <span>{{ $o['validatee'] }}</span>
        </div>
        {{-- eg: $kr adalah 'contextRules' dan $vr adalah array value resultnya --}}
        @foreach ($o['result'] as $kr => $vr)
          @if($vr['structureObjectRuleGroup'])
          <h1>{{ $kr }}</h1>
          <table>
            <thead>
              <tr>
                <th>brDecisionRef</th>
                <th>severityLevel</th>
                <th>objectPath</th>
                <th>use</th>
                <th>line</th>
              </tr>
            </thead>
            <tbody>
              {{-- eg: $vrr adalah array value result <structureObjectRule>  --}}
              @foreach ($vr['structureObjectRuleGroup'] as $vrr)
              <tr>
                <td>{{ $vrr['brDecisionRef'] ? (join(array_map(fn($v) => $v['brDecisionIdentNumber'], $vrr['brDecisionRef']),", ")) : '' }}</td>
                <td>{{ $vrr['brSeverityLevel'] ?? ''  }}</td>
                <td>
                  <table>
                    <thead>
                      <tr>
                        <td>no</td>
                        <td>xpath</td>
                        <td>allowedObjectPath</td>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($vrr['objectPath'] as $i => $objectPath)
                      <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $objectPath['0'] }}</td>
                        <td>{{ $objectPath['at_allowedObjectFlag'] }}</td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </td>
                <td>{{ $vrr['use'] ?? '' }}</td>
                <td>{{ join($vrr['lines'],", ") }}</td>
              </tr>                    
              @endforeach
            </tbody>
          </table>
          @elseif($vr['notationRuleList'])
          <h1>{{ $kr }}</h1>
          <table>
            <thead>
              <tr>
                <th>brDecisionRef</th>
                <th>severityLevel</th>
                <th>notationName</th>
                <th>allowedNotationFlag</th>
                <th>entityName</th>
                <th>systemId</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>{{ $vrr['brDecisionRef'] ? (join(array_map(fn($v) => $v['brDecisionIdentNumber'], $vrr['brDecisionRef']),", ")) : '' }}</td>
                <td>{{ $vrr['brSeverityLevel'] ?? ''  }}</td>
                <td>{{ $vrr['notationName'] ?? ''  }}</td>
                <td>{{ $vrr['allowedNotationFlag'] ?? ''  }}</td>
                <td>{{ $vrr['entityName'] ?? ''  }}</td>
                <td>{{ $vrr['entitySystemId'] ?? ''  }}</td>
              </tr>
            </tbody>
          </table>
          @endif
        @endforeach
      @endforeach
    </p>

    <p>Its recommended to correct your CSDB Object before release. Thankyou</p>
  </div>
  <div style="font-style:italic">Technical Publication Department</div>
</body>
</html>