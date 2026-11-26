import * as echarts from 'echarts';

const numberFormatter = new Intl.NumberFormat('fa-IR');

window.systemReportsChart = function (initialPayload) {
    return {
        chart: null,
        resizeObserver: null,
        payload: initialPayload ?? {rows: []},
        mode: 'users',
        activePoint: null,

        init() {
            this.chart = echarts.init(this.$refs.chart, null, {
                renderer: 'canvas',
                useDirtyRect: true,
            });

            this.renderChart();

            if ('ResizeObserver' in window) {
                this.resizeObserver = new ResizeObserver(() => {
                    this.chart?.resize();
                });

                this.resizeObserver.observe(this.$refs.chart);
            }

            this.chart.on('click', (params) => {
                const row = this.payload.rows?.[params.dataIndex];

                if (!row) {
                    return;
                }

                const isComparison = String(params.seriesName).includes('دوره قبل');

                this.activePoint = {
                    label: isComparison && row.comparison_label
                        ? row.comparison_label
                        : row.period_label,
                    series: params.seriesName,
                    value: Number(params.value ?? 0),
                };
            });
        },

        destroy() {
            this.resizeObserver?.disconnect();
            this.resizeObserver = null;

            if (this.chart && !this.chart.isDisposed()) {
                this.chart.dispose();
            }

            this.chart = null;
        },

        setMode(mode) {
            if (!['users', 'otp', 'engagement'].includes(mode)) {
                return;
            }

            this.mode = mode;
            this.activePoint = null;
            this.renderChart();
        },

        renderChart() {
            if (!this.chart) {
                return;
            }

            this.chart.setOption(this.options(), true);
        },

        options() {
            const rows = this.payload.rows ?? [];
            const labels = rows.map((row) => row.label);
            const showZoom = labels.length > 20;
            const series = this.series();
            const hasData = series.some((item) => item.data.some((value) => Number(value ?? 0) > 0));

            return {
                animationDuration: 500,
                animationEasing: 'cubicOut',
                grid: {
                    top: 68,
                    right: 24,
                    bottom: showZoom ? 78 : 38,
                    left: 28,
                    containLabel: true,
                },
                legend: {
                    top: 10,
                    right: 10,
                    itemWidth: 14,
                    itemHeight: 8,
                    textStyle: {
                        color: '#475569',
                        fontFamily: "'main-font', 'IRANSansX', sans-serif",
                        fontSize: 11,
                    },
                },
                tooltip: {
                    trigger: 'axis',
                    confine: true,
                    backgroundColor: '#0f172a',
                    borderWidth: 0,
                    padding: 12,
                    textStyle: {
                        color: '#fff',
                        fontFamily: "'main-font', 'IRANSansX', sans-serif",
                    },
                    axisPointer: {
                        type: this.mode === 'users' ? 'shadow' : 'line',
                        lineStyle: {color: '#cbd5e1', type: 'dashed'},
                    },
                    formatter: (params) => this.tooltipFormatter(params),
                },
                xAxis: {
                    type: 'category',
                    data: labels,
                    axisLine: {lineStyle: {color: '#e2e8f0'}},
                    axisTick: {show: false},
                    axisLabel: {
                        color: '#64748b',
                        fontFamily: "'main-font', 'IRANSansX', sans-serif",
                        fontSize: 11,
                        hideOverlap: true,
                    },
                },
                yAxis: {
                    type: 'value',
                    min: 0,
                    minInterval: 1,
                    splitNumber: 4,
                    axisLabel: {
                        color: '#94a3b8',
                        fontFamily: "'main-font', 'IRANSansX', sans-serif",
                        formatter: (value) => numberFormatter.format(value),
                    },
                    splitLine: {lineStyle: {color: '#f1f5f9'}},
                },
                dataZoom: showZoom
                    ? [
                        {
                            type: 'inside',
                            start: Math.max(0, 100 - ((20 / labels.length) * 100)),
                            end: 100,
                        },
                        {
                            type: 'slider',
                            height: 20,
                            bottom: 18,
                            borderColor: '#e2e8f0',
                            backgroundColor: '#f8fafc',
                            fillerColor: 'rgba(249, 115, 22, 0.12)',
                            handleStyle: {color: '#f97316'},
                            textStyle: {
                                color: '#94a3b8',
                                fontFamily: "'main-font', 'IRANSansX', sans-serif",
                            },
                        },
                    ]
                    : [],
                graphic: hasData
                    ? []
                    : [{
                        type: 'text',
                        left: 'center',
                        top: 'middle',
                        style: {
                            text: 'در این بازه داده‌ای برای نمایش وجود ندارد',
                            fill: '#94a3b8',
                            font: "13px 'main-font', 'IRANSansX', sans-serif",
                        },
                    }],
                series,
            };
        },

        series() {
            const rows = this.payload.rows ?? [];
            const showSymbols = rows.length <= 20;

            if (this.mode === 'otp') {
                const series = [
                    {
                        name: 'پیامک OTP',
                        type: 'line',
                        smooth: 0.3,
                        showSymbol: showSymbols,
                        symbolSize: 7,
                        data: rows.map((row) => Number(row.otp_messages)),
                        lineStyle: {width: 3, color: '#8b5cf6'},
                        itemStyle: {color: '#8b5cf6'},
                        areaStyle: {color: 'rgba(139, 92, 246, 0.10)'},
                        emphasis: {focus: 'series'},
                    },
                    {
                        name: 'تلاش ناموفق ورود کد',
                        type: 'bar',
                        barMaxWidth: 18,
                        data: rows.map((row) => Number(row.otp_validation_attempts)),
                        itemStyle: {
                            color: '#38bdf8',
                            borderRadius: [6, 6, 0, 0],
                        },
                        emphasis: {focus: 'series'},
                    },
                ];

                if (this.payload.comparisonEnabled) {
                    series.push({
                        name: 'پیامک OTP دوره قبل',
                        type: 'line',
                        smooth: 0.3,
                        showSymbol: false,
                        connectNulls: false,
                        data: rows.map((row) => row.comparison_otp_messages === null
                            ? null
                            : Number(row.comparison_otp_messages)),
                        lineStyle: {width: 2, type: 'dashed', color: '#64748b'},
                        itemStyle: {color: '#64748b'},
                        emphasis: {focus: 'series'},
                    });
                }

                return series;
            }

            if (this.mode === 'engagement') {
                return [
                    {
                        name: 'دیدگاه',
                        type: 'line',
                        smooth: 0.3,
                        showSymbol: showSymbols,
                        data: rows.map((row) => Number(row.comments_count)),
                        lineStyle: {width: 3, color: '#8b5cf6'},
                        itemStyle: {color: '#8b5cf6'},
                        emphasis: {focus: 'series'},
                    },
                    {
                        name: 'تیکت',
                        type: 'line',
                        smooth: 0.3,
                        showSymbol: showSymbols,
                        data: rows.map((row) => Number(row.tickets_count)),
                        lineStyle: {width: 3, color: '#f97316'},
                        itemStyle: {color: '#f97316'},
                        emphasis: {focus: 'series'},
                    },
                    {
                        name: 'دانلود',
                        type: 'bar',
                        barMaxWidth: 20,
                        data: rows.map((row) => Number(row.downloads_count)),
                        itemStyle: {
                            color: '#38bdf8',
                            borderRadius: [6, 6, 0, 0],
                        },
                        emphasis: {focus: 'series'},
                    },
                ];
            }

            const series = [
                {
                    name: 'ثبت نام کاربر',
                    type: 'bar',
                    barMaxWidth: 28,
                    data: rows.map((row) => Number(row.new_users)),
                    itemStyle: {
                        color: '#f97316',
                        borderRadius: [8, 8, 0, 0],
                    },
                    emphasis: {focus: 'series'},
                },
            ];

            if (this.payload.comparisonEnabled) {
                series.push({
                    name: 'ثبت نام دوره قبل',
                    type: 'line',
                    smooth: 0.3,
                    showSymbol: showSymbols,
                    connectNulls: false,
                    data: rows.map((row) => row.comparison_new_users === null
                        ? null
                        : Number(row.comparison_new_users)),
                    lineStyle: {width: 2, type: 'dashed', color: '#64748b'},
                    itemStyle: {color: '#64748b'},
                    emphasis: {focus: 'series'},
                });
            }

            return series;
        },

        tooltipFormatter(params) {
            if (!Array.isArray(params) || params.length === 0) {
                return '';
            }

            const index = params[0].dataIndex;
            const row = this.payload.rows?.[index];
            const lines = [
                `<div style="margin-bottom:8px;font-weight:900">${row?.period_label ?? params[0].axisValueLabel}</div>`,
            ];

            params.forEach((item) => {
                const label = String(item.seriesName).includes('دوره قبل') && row?.comparison_label
                    ? `${item.seriesName} (${row.comparison_label})`
                    : item.seriesName;

                lines.push(
                    `<div style="display:flex;align-items:center;justify-content:space-between;gap:24px;margin-top:5px">`
                    + `<span>${item.marker}${label}</span>`
                    + `<strong>${numberFormatter.format(Number(item.value ?? 0))}</strong>`
                    + `</div>`,
                );
            });

            return lines.join('');
        },

        formatActiveValue() {
            return numberFormatter.format(this.activePoint?.value ?? 0);
        },

        download() {
            if (!this.chart) {
                return;
            }

            const url = this.chart.getDataURL({
                type: 'png',
                pixelRatio: 2,
                backgroundColor: '#ffffff',
            });
            const link = document.createElement('a');
            link.href = url;
            link.download = `Cardiware-system-report-${this.mode}.png`;
            link.click();
        },
    };
};
