import * as echarts from 'echarts';

const numberFormatter = new Intl.NumberFormat('fa-IR');
const compactNumberFormatter = new Intl.NumberFormat('fa-IR', {
    notation: 'compact',
    maximumFractionDigits: 1,
});

window.commerceAnalyticsChart = function (initialPayload) {
    return {
        chart: null,
        resizeObserver: null,
        payload: initialPayload ?? {rows: []},
        mode: 'sales',
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
                    value: params.value,
                    isMoney: this.mode === 'sales',
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
            if (!['sales', 'orders'].includes(mode)) {
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

            this.chart.setOption(
                this.mode === 'orders'
                    ? this.ordersOptions()
                    : this.salesOptions(),
                true,
            );
        },

        salesOptions() {
            const rows = this.payload.rows ?? [];
            const labels = rows.map((row) => row.label);
            const showZoom = labels.length > 20;
            const hasSales = rows.some((row) => Number(row.net_sales) > 0 || Number(row.gross_sales) > 0);

            const series = [
                {
                    name: 'فروش خالص',
                    type: 'line',
                    smooth: 0.3,
                    showSymbol: labels.length <= 20,
                    symbolSize: 7,
                    data: rows.map((row) => Number(row.net_sales)),
                    lineStyle: {width: 3, color: '#f97316'},
                    itemStyle: {color: '#f97316'},
                    areaStyle: {color: 'rgba(249, 115, 22, 0.10)'},
                    emphasis: {focus: 'series'},
                },
                {
                    name: 'فروش ناخالص',
                    type: 'line',
                    smooth: 0.3,
                    showSymbol: false,
                    data: rows.map((row) => Number(row.gross_sales)),
                    lineStyle: {width: 2, color: '#0f172a'},
                    itemStyle: {color: '#0f172a'},
                    emphasis: {focus: 'series'},
                },
                {
                    name: 'تخفیف',
                    type: 'bar',
                    barMaxWidth: 18,
                    data: rows.map((row) => Number(row.discount_amount)),
                    itemStyle: {
                        color: '#c4b5fd',
                        borderRadius: [6, 6, 0, 0],
                    },
                    emphasis: {focus: 'series'},
                },
            ];

            if (this.payload.comparisonEnabled) {
                series.push({
                    name: 'فروش خالص دوره قبل',
                    type: 'line',
                    smooth: 0.3,
                    showSymbol: false,
                    connectNulls: false,
                    data: rows.map((row) => row.comparison_net_sales === null
                        ? null
                        : Number(row.comparison_net_sales)),
                    lineStyle: {
                        width: 2,
                        type: 'dashed',
                        color: '#64748b',
                    },
                    itemStyle: {color: '#64748b'},
                    emphasis: {focus: 'series'},
                });
            }

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
                        fontFamily: "\'main-font\', \'IRANSansXFaNum\', sans-serif",
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
                        fontFamily: "\'main-font\', \'IRANSansXFaNum\', sans-serif",
                    },
                    axisPointer: {
                        type: 'line',
                        lineStyle: {color: '#cbd5e1', type: 'dashed'},
                    },
                    formatter: (params) => this.tooltipFormatter(params, true),
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: true,
                    data: labels,
                    axisLine: {lineStyle: {color: '#e2e8f0'}},
                    axisTick: {show: false},
                    axisLabel: {
                        color: '#64748b',
                        fontFamily: "\'main-font\', \'IRANSansXFaNum\', sans-serif",
                        fontSize: 11,
                        hideOverlap: true,
                    },
                },
                yAxis: {
                    type: 'value',
                    min: 0,
                    splitNumber: 4,
                    axisLabel: {
                        color: '#94a3b8',
                        fontFamily: "\'main-font\', \'IRANSansXFaNum\', sans-serif",
                        formatter: (value) => compactNumberFormatter.format(value),
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
                                fontFamily: "\'main-font\', \'IRANSansXFaNum\', sans-serif",
                            },
                        },
                    ]
                    : [],
                graphic: hasSales
                    ? []
                    : [{
                        type: 'text',
                        left: 'center',
                        top: 'middle',
                        style: {
                            text: 'در این بازه فروش پرداخت شده‌ای ثبت نشده است',
                            fill: '#94a3b8',
                            font: "13px \'main-font\', \'IRANSansXFaNum\', sans-serif",
                        },
                    }],
                series,
            };
        },

        ordersOptions() {
            const rows = this.payload.rows ?? [];
            const labels = rows.map((row) => row.label);
            const showZoom = labels.length > 20;
            const hasOrders = rows.some((row) => Number(row.orders_count) > 0);

            const series = [
                {
                    name: 'سفارش پرداخت شده',
                    type: 'bar',
                    barMaxWidth: 30,
                    data: rows.map((row) => Number(row.orders_count)),
                    itemStyle: {
                        color: '#f97316',
                        borderRadius: [8, 8, 0, 0],
                    },
                    emphasis: {focus: 'series'},
                },
            ];

            if (this.payload.comparisonEnabled) {
                series.push({
                    name: 'سفارش دوره قبل',
                    type: 'line',
                    smooth: 0.3,
                    showSymbol: labels.length <= 20,
                    symbolSize: 6,
                    connectNulls: false,
                    data: rows.map((row) => row.comparison_orders_count === null
                        ? null
                        : Number(row.comparison_orders_count)),
                    lineStyle: {
                        width: 2,
                        type: 'dashed',
                        color: '#64748b',
                    },
                    itemStyle: {color: '#64748b'},
                    emphasis: {focus: 'series'},
                });
            }

            return {
                animationDuration: 500,
                animationEasing: 'cubicOut',
                grid: {
                    top: 62,
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
                        fontFamily: "\'main-font\', \'IRANSansXFaNum\', sans-serif",
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
                        fontFamily: "\'main-font\', \'IRANSansXFaNum\', sans-serif",
                    },
                    axisPointer: {type: 'shadow'},
                    formatter: (params) => this.tooltipFormatter(params, false),
                },
                xAxis: {
                    type: 'category',
                    data: labels,
                    axisLine: {lineStyle: {color: '#e2e8f0'}},
                    axisTick: {show: false},
                    axisLabel: {
                        color: '#64748b',
                        fontFamily: "\'main-font\', \'IRANSansXFaNum\', sans-serif",
                        fontSize: 11,
                        hideOverlap: true,
                    },
                },
                yAxis: {
                    type: 'value',
                    minInterval: 1,
                    min: 0,
                    splitNumber: 4,
                    axisLabel: {
                        color: '#94a3b8',
                        fontFamily: "\'main-font\', \'IRANSansXFaNum\', sans-serif",
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
                                fontFamily: "\'main-font\', \'IRANSansXFaNum\', sans-serif",
                            },
                        },
                    ]
                    : [],
                graphic: hasOrders
                    ? []
                    : [{
                        type: 'text',
                        left: 'center',
                        top: 'middle',
                        style: {
                            text: 'در این بازه سفارش پرداخت شده‌ای ثبت نشده است',
                            fill: '#94a3b8',
                            font: "13px \'main-font\', \'IRANSansXFaNum\', sans-serif",
                        },
                    }],
                series,
            };
        },

        tooltipFormatter(params, isMoney) {
            const points = Array.isArray(params) ? params : [params];
            const dataIndex = points[0]?.dataIndex ?? 0;
            const row = this.payload.rows?.[dataIndex] ?? {};

            const lines = [
                `<div style="font-weight:800;margin-bottom:8px">${row.period_label ?? ''}</div>`,
            ];

            points.forEach((point) => {
                const isComparison = String(point.seriesName).includes('دوره قبل');
                const comparisonLabel = isComparison && row.comparison_label
                    ? `<div style="color:#cbd5e1;font-size:11px;margin:2px 0 5px">${row.comparison_label}</div>`
                    : '';
                const value = isMoney
                    ? `${numberFormatter.format(Number(point.value ?? 0))} تومان`
                    : numberFormatter.format(Number(point.value ?? 0));

                lines.push(
                    `<div style="display:flex;align-items:center;justify-content:space-between;gap:24px;margin-top:5px">`
                    + `<span>${point.marker}${point.seriesName}</span>`
                    + `<strong>${value}</strong>`
                    + `</div>${comparisonLabel}`,
                );
            });

            return lines.join('');
        },

        formatActiveValue() {
            if (!this.activePoint) {
                return '';
            }

            const value = numberFormatter.format(Number(this.activePoint.value ?? 0));

            return this.activePoint.isMoney
                ? `${value} تومان`
                : value;
        },

        download() {
            if (!this.chart) {
                return;
            }

            const link = document.createElement('a');
            link.download = `moddingway-analytics-${this.mode}.png`;
            link.href = this.chart.getDataURL({
                type: 'png',
                pixelRatio: 2,
                backgroundColor: '#ffffff',
            });
            link.click();
        },
    };
};
