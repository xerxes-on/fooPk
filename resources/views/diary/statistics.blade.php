@extends('layouts.app')

@section('title', trans('common.diary_statistics'))

<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"/>

@section('content')
    @php $chartsArray = [
        'weight'    => trans('common.weight'),
        'waist'     => trans('common.waist'),
        'upper_arm' => trans('common.upper_arm'),
        'leg'       => trans('common.leg'),
        'mood'      => trans('common.mood')
    ]; @endphp

    <div class="container">
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-xs-12">
                <a href="{{ route('diary.create') }}" class="btn btn-tiffany pull-right hidden-xs">
                    <img src="{{ asset("/images/icons/ic_add_white.svg") }}" alt="" role="presentation"/>
                    {{ trans('common.enter_data') }}
                </a>

                <!-- Nav content list -->
                <ul class="content-links" role="navigation">
                    <li class="content-links_item {{ active('diary.statistics') }}">
                        <a href="{{ route('diary.statistics') }}">{{ trans('common.diary_statistics') }}</a>
                    </li>
                    <li class="content-links_item {{ active('posts.list') }}">
                        <a href="{{ route('posts.list') }}">{{ trans('common.posts') }}</a>
                    </li>
                </ul>

                <a href="{{ route('diary.create') }}" class="btn btn-tiffany pull-left visible-xs">
                    <img src="{{ asset("/images/icons/ic_add_white.svg") }}" alt="" role="presentation"/>
                    {{ trans('common.enter_data') }}
                </a>
            </div>

            <div class="col-xs-6">
                <div id="diaryRange"
                     style="background: #fff; cursor: pointer; padding: 5px 10px; width: 100%">
                    <i class="fa fa-calendar" aria-hidden="true"></i>&nbsp;
                    <span></span>
                    <i class="fa fa-caret-down" aria-hidden="true"></i>
                </div>
            </div>
        </div>

        @foreach($chartsArray as $key => $chart)
            <div class="row">
                <div class="col-xs-12">
                    <div class="fixed-height-chart" style="height: 300px; position: relative; margin-bottom: 20px;">
                        <div class="loadingMessage"
                             style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                        </div>
                        <canvas id="chart_{{ $key }}"></canvas>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="row">
            <div class="col-xs-12">
                <div class="image-thumbs">
                    {{--TODO: Unoptimized queries here--}}
                    @foreach($diaryData as $item)
                        @if (!empty($item->image_file_name))
                            <img src="{{ $item->image->url('thumb') }}" alt="{{ $item->image_file_name }}"
                                 class="img-thumbnail">
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript" src="//cdn.jsdelivr.net/npm/moment@latest/moment.min.js"></script>
    <script type="text/javascript" src="//cdn.jsdelivr.net/npm/moment@latest/locale/de.js"></script>
    <script type="text/javascript" src="//cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script type="text/javascript" src="{{ mix('/vendor/chart-js/Chart.min.js') }}"></script>

    <script type="text/javascript">
        {{-- TODO: move it out to main scripts --}}
        moment.locale('{{ app()->getLocale() }}');

        let $chartsArray = $.parseJSON('{!! json_encode($chartsArray) !!}'),
            $start = moment().subtract(14, 'days'),
            $end = moment().add(1, 'days');

        $(document).ready(function () {

            $('#diaryRange').daterangepicker({
                startDate: $start,
                endDate: $end,
                ranges: {
                    '{{ trans('common.today') }}': [moment(), moment()],
                    '{{ trans('common.yesterday') }}': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    '{{ trans('common.last_7_days') }}': [moment().subtract(6, 'days'), moment()],
                    '{{ trans('common.last_30_days') }}': [moment().subtract(29, 'days'), moment()],
                    '{{ trans('common.this_month') }}': [moment().startOf('month'), moment().endOf('month')],
                    '{{ trans('common.last_month') }}': [
                        moment().subtract(1, 'month').startOf('month'),
                        moment().subtract(1, 'month').endOf('month')],
                },
                locale: {
                    format: 'MM.DD.YYYY',
                    separator: ' - ',
                    applyLabel: "{{ trans('common.apply') }}",
                    cancelLabel: "{{ trans('common.cancel') }}",
                    customRangeLabel: "{{ trans('common.custom') }}",
                    daysOfWeek: [
                        "{{ trans('common.su') }}",
                        "{{ trans('common.mo') }}",
                        "{{ trans('common.tu') }}",
                        "{{ trans('common.we') }}",
                        "{{ trans('common.th') }}",
                        "{{ trans('common.fr') }}",
                        "{{ trans('common.sa') }}",
                    ],
                    monthNames: [
                        "{{ trans('common.january') }}",
                        "{{ trans('common.february') }}",
                        "{{ trans('common.march') }}",
                        "{{ trans('common.april') }}",
                        "{{ trans('common.may') }}",
                        "{{ trans('common.june') }}",
                        "{{ trans('common.july') }}",
                        "{{ trans('common.august') }}",
                        "{{ trans('common.september') }}",
                        "{{ trans('common.october') }}",
                        "{{ trans('common.november') }}",
                        "{{ trans('common.december') }}",
                    ],
                    firstDay: 1,
                },
            }, function (start, end, label) {
                //console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
                getChartData(start, end);
            });

            getChartData($start, $end);
        });

        function renderChart(min, max, chartData) {
            $.each($chartsArray, function ($chart, label) {
                let $datasetData = [];

                chartData.forEach(function (data) {
                    if (data[$chart] != null) $datasetData.push({x: data.created_at, y: data[$chart]});
                });

                let myChart = new Chart(document.getElementById('chart_' + $chart), {
                    type: 'line',
                    data: {
                        datasets: [
                            {
                                label: label,
                                data: $datasetData,
                                backgroundColor: [
                                    'rgba(255, 99, 132, 0.2)',
                                    'rgba(54, 162, 235, 0.2)',
                                    'rgba(255, 206, 86, 0.2)',
                                    'rgba(75, 192, 192, 0.2)',
                                    'rgba(153, 102, 255, 0.2)',
                                    'rgba(255, 159, 64, 0.2)',
                                ],
                                borderColor: [
                                    'rgba(255,99,132,1)',
                                    'rgba(54, 162, 235, 1)',
                                    'rgba(255, 206, 86, 1)',
                                    'rgba(75, 192, 192, 1)',
                                    'rgba(153, 102, 255, 1)',
                                    'rgba(255, 159, 64, 1)',
                                ],
                                borderWidth: 3,
                                pointRadius: 5,
                                pointHoverRadius: 5,
                            }],
                    },
                    options: {
                        maintainAspectRatio: false,
                        scales: {
                            xAxes: [
                                {
                                    type: 'time',
                                    time: {
                                        parser: 'YYYY-MM-DD',
                                        unit: 'day',
                                        unitStepSize: 14,
                                        // tooltipFormat: "DD.MM.YYYY",
                                        displayFormats: {
                                            day: 'DD.MM',
                                        },
                                        min: min.format('YYYY-MM-DD'),
                                        max: max.format('YYYY-MM-DD'),
                                    },
                                    ticks: {
                                        //source: 'data'
                                    },
                                }],
                            yAxes: [
                                {
                                    ticks: {
                                        min: 0,
                                        //userCallback: function(v) { return '######'; },
                                        //max: 10,
                                        //stepSize: 1
                                    },
                                }],
                        },
                        tooltips: {
                            enabled: false,
                            // callbacks: {
                            //     label: function(tooltipItem, data) {
                            //         return data.datasets[tooltipItem.datasetIndex].label + ': ' + tooltipItem.yLabel.toString().replace('.', ',');
                            //     },
                            // }
                        },

                        onClick: function clickHandler(evt) {
                            const datasetIndex = myChart.getElementAtEvent(event)[0]._datasetIndex;
                            if (datasetIndex >= 0) {
                                var activePoints = myChart.getElementsAtEventForMode(evt, 'point', myChart.options);
                                var firstPoint = activePoints[0];
                                var label = myChart.data.labels[firstPoint._index];
                                var value = myChart.data.datasets[firstPoint._datasetIndex].data[firstPoint._index].x;
                                var dat = moment(value).format('YYYY-MM-DD');
                            }
                            // alert(myChart.data.datasets[datasetIndex].label + '\n' + label + ": " + myChart.data.datasets[datasetIndex].yLabel);
                            // console.log(dat);
                            window.location.href = "{{ url('user/diary/edit')}}" + '/' + dat;
                        },
                        legend: {
                            display: true,
                        },
                        animations: {
                            tension: {
                                duration: 1000,
                                easing: 'linear',
                            },
                        },
                        hover: {
                            animationDuration: 0,
                        },
                        responsiveAnimationDuration: 0,
                    },
                    /*plugins: [{
                        beforeInit: function(chart) {
                            var time = chart.options.scales.xAxes[0].time, // 'time' object reference
                                timeDiff = moment(time.max).diff(moment(time.min), 'd'); // difference (in days) between min and max date
                            // populate 'labels' array
                            // (create a date string for each date between min and max, inclusive)
                            for (i = 0; i <= timeDiff; i++) {
                                var _label = moment(time.min).add(i, 'd').format('YYYY-MM-DD');
                                chart.data.labels.push(_label);
                            }
                        }
                    }]*/
                });
                if ($chart === 'weight') {
                    myChart.options.scales.yAxes[0].ticks.min = 30;
                    // myChart.options.scales.yAxes[0].ticks.stepSize = 1;
                    myChart.update();
                }

                if ($chart === 'mood') {
                    myChart.options.scales.yAxes[0].ticks.max = 10;
                    myChart.options.scales.yAxes[0].ticks.stepSize = 1;

                    myChart.update();
                }
            });
        }

        function getChartData(start, end) {

            $('#diaryRange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

            $('.loadingMessage').html('<i class="fa fa-spinner fa-spin fa-2x" aria-hidden="true"></i>');

            $.ajax({
                type: 'GET',
                url: "{{ route('diary.statistics.chartdata') }}",
                dataType: 'json',
                data: {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    start: start.format('YYYY-MM-DD'),
                    end: end.format('YYYY-MM-DD'),
                },
                success: function (result) {
                    if (result.success === true) {
                        $('.loadingMessage').html('');
                        renderChart(start, end, result.data);
                    } else {
                        alert(result.message);
                    }
                },
                error: function (data) {
                    console.log(data);
                },
            });
        }
    </script>
@endsection