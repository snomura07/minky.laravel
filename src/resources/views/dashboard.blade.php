<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        :root {
            --bg: #f3f6fd;
            --surface: #ffffff;
            --text-main: #1d2c49;
            --text-sub: #65789c;
            --line: #2f7dfa;
            --grid: #d9e5fa;
            --border: #dce7fb;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            color: var(--text-main);
            font-family: "BIZ UDPGothic", "Yu Gothic UI", "Hiragino Kaku Gothic ProN", sans-serif;
            background: radial-gradient(circle at top left, #eaf2ff, #f8fbff 55%, #f0f6ff);
        }

        .wrap {
            max-width: 1100px;
            margin: 0 auto;
            padding: 32px 16px 48px;
        }

        .panel {
            background: linear-gradient(135deg, #f4f7fe 0%, #e9f1ff 100%);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 18px 40px rgba(33, 72, 159, 0.08);
        }

        h1 {
            margin: 0;
            font-size: clamp(1.6rem, 2.5vw, 2.1rem);
            letter-spacing: 0.02em;
        }

        .sub {
            margin: 6px 0 0;
            color: var(--text-sub);
            font-size: 0.95rem;
        }

        .date-form {
            margin-top: 14px;
            display: flex;
            align-items: end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .date-form label {
            display: grid;
            gap: 6px;
            color: var(--text-sub);
            font-size: 0.85rem;
            font-weight: 700;
        }

        .date-form input[type="date"] {
            padding: 8px 10px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: #fff;
            color: var(--text-main);
        }

        .date-form button {
            padding: 9px 14px;
            border: 1px solid #2f7dfa;
            border-radius: 10px;
            background: #2f7dfa;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }

        .cards {
            margin-top: 20px;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 16px;
        }

        .card .label {
            margin: 0;
            color: var(--text-sub);
            font-size: 0.82rem;
            letter-spacing: 0.08em;
            font-weight: 700;
        }

        .card .value {
            margin: 8px 0 0;
            font-size: clamp(1.7rem, 2.8vw, 2.1rem);
            font-weight: 700;
        }

        .card.max .value { color: #d7542b; }
        .card.min .value { color: #2566d8; }

        .meta {
            margin-top: 8px;
            color: var(--text-sub);
            font-size: 0.86rem;
        }

        .chart-panel {
            margin-top: 20px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 18px;
        }

        .chart-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
        }

        .chart-meta {
            margin: 6px 0 0;
            color: var(--text-sub);
            font-size: 0.85rem;
        }

        .chart-area {
            margin-top: 14px;
            height: 300px;
        }

        #temperature-chart {
            width: 100%;
            height: 100%;
            display: block;
        }

        .empty {
            margin-top: 14px;
            color: var(--text-sub);
            font-size: 0.92rem;
        }

        @media (max-width: 640px) {
            .wrap { padding: 20px 12px 32px; }
            .panel { padding: 16px; border-radius: 16px; }
            .cards { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <main class="wrap">
        <section class="panel">
            <h1>{{ $title }}</h1>
            <p class="sub">単日または期間を指定して、気温推移と最高・最低気温を表示します（最大183日）。</p>
            <form class="date-form" method="get" action="{{ route('dashboard') }}">
                <label>
                    開始日
                    <input type="date" name="from" value="{{ $selectedFrom }}">
                </label>
                <label>
                    終了日
                    <input type="date" name="to" value="{{ $selectedTo }}">
                </label>
                <button type="submit">表示更新</button>
            </form>

            @if ($dayStats)
                <section class="cards">
                    <article class="card max">
                        <p class="label">最高気温</p>
                        <p class="value">{{ is_null($dayStats['max_temperature']) ? '--' : number_format($dayStats['max_temperature'], 1).'℃' }}</p>
                    </article>
                    <article class="card min">
                        <p class="label">最低気温</p>
                        <p class="value">{{ is_null($dayStats['min_temperature']) ? '--' : number_format($dayStats['min_temperature'], 1).'℃' }}</p>
                    </article>
                </section>
                <p class="meta">対象期間: {{ $periodLabel }}</p>
            @else
                <p class="empty">選択期間の気温データが存在しません。</p>
            @endif

            <section class="chart-panel">
                <h2 class="chart-title">気温推移</h2>
                <p class="chart-meta">対象地域: 福井（city_id: 2）</p>
                <div class="chart-area">
                    <canvas id="temperature-chart" data-chart='@json($chartData)'></canvas>
                </div>
                @if ($chartData->isEmpty())
                    <p class="empty">選択期間の気温データがありません。</p>
                @endif
            </section>
        </section>
    </main>

    <script>
        (function () {
            const canvas = document.getElementById('temperature-chart');
            if (!canvas) return;

            let points = [];
            try {
                points = JSON.parse(canvas.dataset.chart || '[]');
            } catch (e) {
                points = [];
            }
            if (!Array.isArray(points) || points.length === 0) return;

            const samplePoints = (source, maxPoints) => {
                if (!Array.isArray(source) || source.length <= maxPoints) return source;
                const sampled = [];
                const used = new Set();
                const step = (source.length - 1) / (maxPoints - 1);
                for (let i = 0; i < maxPoints; i += 1) {
                    const idx = Math.round(i * step);
                    if (used.has(idx)) continue;
                    used.add(idx);
                    sampled.push(source[idx]);
                }
                if (sampled[sampled.length - 1] !== source[source.length - 1]) {
                    sampled[sampled.length - 1] = source[source.length - 1];
                }
                return sampled;
            };

            const ctx = canvas.getContext('2d');
            const ratio = window.devicePixelRatio || 1;
            const width = canvas.clientWidth;
            const height = canvas.clientHeight;
            canvas.width = Math.floor(width * ratio);
            canvas.height = Math.floor(height * ratio);
            ctx.scale(ratio, ratio);

            const pad = { top: 16, right: 18, bottom: 28, left: 42 };
            const chartW = width - pad.left - pad.right;
            const chartH = height - pad.top - pad.bottom;

            const values = points.map((p) => Number(p.temperature)).filter(Number.isFinite);
            if (values.length === 0) return;
            const maxPointCount = Math.max(24, Math.min(360, Math.floor(chartW / 6)));
            const plotPoints = samplePoints(points, maxPointCount);

            const min = Math.min(...values);
            const max = Math.max(...values);
            const range = Math.max(max - min, 1);

            ctx.clearRect(0, 0, width, height);
            ctx.font = '12px "BIZ UDPGothic", "Yu Gothic UI", sans-serif';

            for (let i = 0; i <= 4; i += 1) {
                const y = pad.top + (chartH / 4) * i;
                ctx.strokeStyle = '#d9e5fa';
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.moveTo(pad.left, y);
                ctx.lineTo(width - pad.right, y);
                ctx.stroke();

                const temp = (max - (range / 4) * i).toFixed(1);
                ctx.fillStyle = '#65789c';
                ctx.fillText(temp + '℃', 4, y + 4);
            }

            const xAt = (idx) => plotPoints.length === 1
                ? pad.left + chartW / 2
                : pad.left + (chartW * idx) / (plotPoints.length - 1);
            const yAt = (value) => pad.top + ((max - value) / range) * chartH;

            const grad = ctx.createLinearGradient(0, pad.top, 0, height - pad.bottom);
            grad.addColorStop(0, 'rgba(47, 125, 250, 0.34)');
            grad.addColorStop(1, 'rgba(47, 125, 250, 0.02)');

            ctx.beginPath();
            plotPoints.forEach((p, i) => {
                const x = xAt(i);
                const y = yAt(Number(p.temperature));
                if (i === 0) ctx.moveTo(x, y);
                else ctx.lineTo(x, y);
            });
            ctx.lineTo(xAt(plotPoints.length - 1), height - pad.bottom);
            ctx.lineTo(xAt(0), height - pad.bottom);
            ctx.closePath();
            ctx.fillStyle = grad;
            ctx.fill();

            ctx.beginPath();
            plotPoints.forEach((p, i) => {
                const x = xAt(i);
                const y = yAt(Number(p.temperature));
                if (i === 0) ctx.moveTo(x, y);
                else ctx.lineTo(x, y);
            });
            ctx.strokeStyle = '#2f7dfa';
            ctx.lineWidth = 2.5;
            ctx.stroke();

            if (plotPoints.length <= 120) {
                ctx.fillStyle = '#2f7dfa';
                plotPoints.forEach((p, i) => {
                    ctx.beginPath();
                    ctx.arc(xAt(i), yAt(Number(p.temperature)), 3, 0, Math.PI * 2);
                    ctx.fill();
                });
            }

            ctx.fillStyle = '#65789c';
            const tickCount = Math.max(2, Math.min(8, Math.floor(chartW / 120) + 1));
            const tickStep = plotPoints.length === 1 ? 1 : (plotPoints.length - 1) / (tickCount - 1);
            const tickIndices = [];
            for (let i = 0; i < tickCount; i += 1) {
                const idx = Math.round(i * tickStep);
                if (tickIndices[tickIndices.length - 1] !== idx) {
                    tickIndices.push(idx);
                }
            }

            tickIndices.forEach((idx, i) => {
                const x = xAt(idx);
                if (i === 0) ctx.textAlign = 'left';
                else if (i === tickIndices.length - 1) ctx.textAlign = 'right';
                else ctx.textAlign = 'center';
                ctx.fillText(plotPoints[idx].time || '', x, height - 8);
            });
        }());
    </script>
</body>
</html>
