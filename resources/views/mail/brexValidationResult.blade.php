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
        @foreach ($o['results'] as $kr => $vr)
          <h1>{{ $kr }}</h1>
          @if(isset($vr['structureObjectRuleGroup']) && $vr['structureObjectRuleGroup'])
          <h2>Structure Object Rule</h2>
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
                <td>{{ isset($vrr['brDecisionRef']) && $vrr['brDecisionRef'] ? (join(", ", array_values(array_filter($vrr['brDecisionRef'], fn($v,$k) => $k === 'brDecisionIdentNumber' ? $v : false ,ARRAY_FILTER_USE_BOTH)))) : '' }}</td>
                <td>{{ $vrr['brSeverityLevel'] ?? ''  }}</td>
                <td>
                  <div>xpath: <span class="xpath">{{ $vrr['objectPath']['0'] }}</span></div>
                  <div>allwoedObjectPath: <span class="allowedObjectPath">{{ $vrr['objectPath']['at_allowedObjectFlag'] }}</span></div>
                </td>
                <td>{{ $vrr['use'] ?? '' }}</td>
                <td>{{ join(", ", $vrr['lines']) }}</td>
              </tr>                    
              @endforeach
            </tbody>
          </table>
          @endif
          @if(isset($vr['notationRuleList']) && $vr['notationRuleList'])
          <h2>Notation Rule</h2>
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
              @foreach ($vr['notationRuleList'] as $vrr)
              <tr>
                <td>{{ isset($vrr['brDecisionRef']) && $vrr['brDecisionRef'] ? (join(array_map(fn($v) => $v['brDecisionIdentNumber'], $vrr['brDecisionRef']),", ")) : '' }}</td>
                <td>{{ isset($vrr['brSeverityLevel']) ? ($vrr['brSeverityLevel']) : ''  }}</td>
                <td>{{ isset($vrr['notationName']) ? ($vrr['notationName']) : ''  }}</td>
                <td>{{ isset($vrr['allowedNotationFlag']) ? ($vrr['allowedNotationFlag']) : ''  }}</td>
                <td>{{ isset($vrr['entityName']) && count($vrr['entityName']) ? (join(", ", $vrr['entityName'])) : ''  }}</td>
                <td>{{ isset($vrr['entitySystemId']) && count($vrr['entitySystemId']) ? (join(", ", $vrr['entitySystemId'])) : ''  }}</td>
              </tr>
              @endforeach
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