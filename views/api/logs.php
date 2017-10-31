<style type="text/css">
.api-logs {
    padding: 20px;
}
.api-logs table {
    width: 100%;
    max-width: 100%;
    border-collapse: collapse;
    border: 5px solid #E0E0E0;
    table-layout: fixed;
}
.api-logs table td,
.api-logs table th {
    text-align: left;
    padding: 3px 5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-width: 80px;
}
.api-logs table td {
    background-color: #E9E9E9;
}
.api-logs table th {
    background-color: #E0E0E0;
    font-weight: bold;
}
.api-logs table tfoot th {
    font-weight: normal;
    padding-top: 8px;
}
.api-logs table thead th {
    padding-bottom: 8px;
}
.api-logs table td:nth-child(odd) {
    background-color: #F3F3F3;
}
.api-logs .popup {
    cursor: pointer;
    font-weight: bold;

}
body div.api-popup div.content div.body {
    padding: 0;
}
body div.api-popup div.content div.body div.buttons,
body div.api-popup div.content div.body div.message {
    margin: 0;
    padding: 0;
}
body div.api-popup div.content div.body div.buttons a {
    margin-top: 5px;
    margin-bottom: 5px;
}
.api-popup-inner {
    max-width: 100%;
    max-height: 80vh;
    padding: 15px;
    overflow: auto;
    font-size: 0.85em;
}
</style>
<?php
$popup = function ($title, $data) {
    return safe(
        '$.alert(' . json_encode($title)
        . ', ' . json_encode(
            '<div class="api-popup-inner">'
            . Debug::vars($data)
            . '</div>'
        ) . ', null, \'api-popup\');'
    );
};
$totals = [
    'duration' => 0,
];
?>
<div class="api-logs" id="api-logs">
    <h1>API requests</h1>
    <table>
        <col width="40" />
        <col width="120" />
        <col width="80" />
        <col width="80" />
        <col />
        <col width="120" />
        <col width="100" />
        <col width="100" />

        <thead>
            <tr>
                <th>&nbsp;</th>
                <th>Start</th>
                <th>Duration</th>
                <th>Method</th>
                <th>URL</th>
                <th>Post data</th>
                <th>Status</th>
                <th>Response</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($api->logs() as $index => $log): ?>
                <?php
                // Prepare vars
                $httpCode = Arr::path($log, 'curlInfo.http_code', 400);
                $start = DateTime::createFromFormat('U.u', $log['start']);
                $start = $start->format('H:i:s')
                    . '.' . substr(
                        sprintf('%0.3F', (float) ('0.' . $start->format('u'))),
                        2
                    )
                ;

                // Url
                $url = $log['url'];
                $urlQueryPos = strpos($url, '?');
                if ($urlQueryPos === false) {
                    $urlQueryPos = strlen($url);
                }

                // Parse response
                $response = Arr::get($log, 'response');
                try {
                    $response = json_decode($response);
                    if (!$response) {
                        $response = Arr::get($log, 'response');
                    }
                } catch (Exception $e) {}

                // Update totals
                $totals['duration'] += $log['duration'];
                ?>
                <tr>
                    <td class="u-align-right"><?= safe($index + 1) ?></td>
                    <td class="u-align-right" title="<?= safe($start) ?>"><?= safe($start) ?></td>
                    <td class="u-align-right">
                        <?php if ($log['fromCache']): ?>
                            <span style="color: blue;">
                                <?= safe(sprintf('%0.3Fs', $log['duration'])) ?>
                            </span>
                        <?php else: ?>
                            <?= safe(sprintf('%0.3Fs', $log['duration'])) ?>
                        <?php endif; ?>
                    </td>
                    <td><?= safe($log['method']) ?></td>
                    <td><a
                        href="<?= safe($url) ?>"
                        target="_blank"
                        title="<?= safe($url) ?>"
                    >
                        <strong><?= safe(substr($url, 0, $urlQueryPos)) ?></strong><?= safe(substr($url, $urlQueryPos)) ?>
                    </a></td>
                    <td class="u-align-right">
                        <?= Text::bytes(strlen($log['data'])) ?>
                        <span
                            class="popup"
                            onclick="<?= $popup('POST data', $log['data']) ?>"
                        >(data)</span>
                    </td>
                    <td class="u-align-right">
                        <?php if ($log['fromCache']): ?>
                            <span style="color: blue;">000</span>
                        <?php elseif ($httpCode >= 200 && $httpCode < 300): ?>
                            <span style="color: green;"><?= safe($httpCode) ?></span>
                        <?php elseif ($httpCode < 400): ?>
                            <span style="color: orange;"><?= safe($httpCode) ?></span>
                        <?php else: ?>
                            <span style="color: red;"><?= safe($httpCode) ?></span>
                        <?php endif; ?>
                        <span
                            class="popup"
                            onclick="<?= $popup('CURL info', Arr::merge(
                                ['curlError' => $log['curlError']],
                                $log['curlInfo']
                            )) ?>"
                        >(info)</span>
                    </td>
                    <td>
                        <span
                            class="popup"
                            onclick="<?= $popup('Response', $response) ?>"
                        >(response)</span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th>&nbsp;</th>
                <th class="u-align-right"><strong>Total:</strong></th>
                <th><?= safe(sprintf('%0.3Fs', $totals['duration'])) ?></th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
            </tr>
        </tfoot>
    </table>
</div>
