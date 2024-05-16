<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;700&display=swap">
    <style>
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Quicksand', sans-serif;
            position: relative;
        }

        .d-flex {
            display: flex;
        }

        .col-5 {
            width: 50%;
        }

        .head-left {
            padding: 40px;
            background: #d7d7d7;
            text-align: right;
            border-radius: 0 0 10px 0;
        }

        .text-white {
            color: white;
        }

        .font-weight-normal {
            font-weight: 500;
        }

        .font-weight-light {
            font-weight: 400;
        }

        .align-items-baseline {
            align-items: flex-end;
        }

        .align-items-center {
            align-items: center;
        }

        .head-right {
            padding-right: 120px;
        }

        .head-right img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            object-position: center center;
            border-radius: 6px;
        }

        .text-right {
            text-align: right;
        }

        .content {
            padding: 60px 30px 100px;
            border-color: #d7d7d7;
            border-width: 0 0 1px 1px;
            border-style: solid;
            position: relative;
            border-radius: 0 0 0 8px;
        }

        .container {
            width: 100%;
            padding-left: 120px;
            padding-right: 120px;
            margin-left: auto;
            margin-right: auto;
        }

        .created {
            width: 200px;
            position: absolute;
            bottom: 100px;
            left: -115px;
            transform: rotate(-90deg);
            color: #8b948b;
            font-size: 12px;
        }

        .main-content {
            color: #727475;
        }

        .text-center {
            text-align: center;
        }

        .list-options {
            list-style: none;
        }

        .item-option {
            padding: 16px 0;
            position: relative;
        }

        .item-option img {
            position: absolute;
            left: 0;
            width: 32px;
            object-fit: contain;
            object-position: center;
        }

        .item-option p {
            font-size: 20px;
            color: #727475;
            position: relative;
            left: 60px;
        }

        .break-line {
            width: 150px;
            height: 2px;
            background: #d7d7d7;
            margin: 40px auto;
        }

        footer {
            position: relative;
            padding-bottom: 50px;
        }

        footer ul {
            justify-content: center;
            margin-top: 10px;
            list-style: none;
        }

        .box-success {
            background: #79b173;
        }

        .box-danger {
            background: #ec6f6f;
        }

        .box-warning {
            background: #f4ea5f;
        }

        .box-info {
            background: #babdc8;
        }

        .box-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            color: #000;
            margin-right: 4px;
            content: '';
        }

        .box-item {
            margin: 0 8px;
        }

        .footer-logo {
            display: flex;
            position: absolute;
            right: 0;
            bottom: 0;
        }

        .footer-logo img {
            width: 200px;
            height: 105px;
            object-fit: contain;
            object-position: center center;
            border-radius: 10px 0 0 0;
        }

        .main-title {
            font-weight: 500;
            font-size: 1.75em;
            margin-bottom: 40px;
        }

        @media(max-width: 991px) {
            footer ul {
                flex-wrap: wrap;
            }

            .box-item {
                margin: 4px 0;
                width: calc(50% - 16px);
            }
        }

        @media(max-width: 767px) {
            .footer-logo {
                position: relative;
                text-align: right;
            }

            footer {
                padding-bottom: 0;
            }

            .container {
                padding-left: 40px;
                padding-right: 40px;
            }

            .head-right {
                padding-right: 40px;
            }

            .head-left {
                padding: 40px 20px;
            }
        }

        @media(max-width: 575px) {
            .item-option img {
                width: 30px;
                height: 30px;
            }

            .box-item {
                width: 100%;
            }

            .content {
                padding: 40px 20px 80px;
            }

            .head-left h1 {
                text-align: center;
            }

            .head-left {
                border-radius: 0 0 10px 10px;
            }

            .head-left,
            .head-right {
                width: 100%;
            }

            .head-right {
                padding-right: 0;
                text-align: center;
                margin: 20px 0;
            }

            .header-content {
                flex-wrap: wrap;
            }
        }

        .task-list {
            flex-direction: column;
        }

        .task-list .box-item {
            margin: 12px 0;
        }

        .align-items-start {
            align-items: flex-start;
        }

        .mb-2 {
            margin-bottom: 8px;
        }

        .task-des {
            margin-left: 4px;
            font-size: 20px;
            color: #727475;
        }

        .task-comment {
            font-size: 18px;
        }
    </style>
</head>

<body>
    <div id="app">
        <header>
            <div class="d-flex align-items-baseline header-content">
                <div class="head-left col-5">
                    <h1 class="font-weight-light text-white">{{ optional($project->company)->name }}</h1>
                </div>
                <div class="head-right col-5 text-right">
                    <?php $logo = optional($project->company)->logo; ?>
                    @if ($logo)
                        <img src="{{ asset(Storage::url($logo)) }}" alt="logo">
                    @endif
                </div>
            </div>
        </header>
        <!-- end header -->
        <main class="container">
            <div class="content">
                <div class="main-content">
                    <h2 class="main-title font-weight-normal text-center">{{ $project->title }} Report</h2>
                    <ul class="list-options">
                        <li class="item-option d-flex align-items-center">
                            <img src="data:image/png;base64,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"
                                class="" alt="icon" />
                            <p class="font-weight-light">{{ Date('d/m/Y', strtotime($project->start_date)) }} to
                                {{ Date('d/m/Y', strtotime($project->end_date)) }}</p>
                        </li>
                        <li class="item-option d-flex align-items-center">
                            <img src="data:image/png;base64,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"
                                class="" alt="icon" />
                            <p class="font-weight-light">{{ $project->description }}</p>
                        </li>
                        <li class="item-option d-flex align-items-center">
                            <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEgAAABQCAYAAAC6aDOxAAAPM0lEQVR4nNVc+bMdRRX+zn33LXlZSB4BEiIQsiAECCCLkUVjUAsoKBWh/NEql39IfvYHrLK00MIFJRZugAooCSJKAZFERTbDEkJICFnesU73NzPdPT1z73tzbxJP6lbu65np5fTps3znzJX77vs2Rkmq2c56EEwC6AO4AIqPA7gGwJUAzgdwLgTTAKahEOsGwIcQHAHwJhR7ATwPYJeI2Pd3FXocipN2r4i4QRT5wbtQf+Q95snG2QbgegCfBLAOwGoAKwHMApgBMJE8aes9QQZugrjn7wD0XwCeAPAHAHtOxcTHSUsArAVwA4DbAHwKwCVDjmdiMcnPcjJ1C4B5AJcD2AjgMQDPAdjP9v87Bm0U4KsAbjcpUMiMb/bnCCLQ4kyGp0NQa5P461Yy6DoAPwDwMwDvjGMB42LQEkqNMWcH4HRObt0Nja0kPJazIthOCTsXwM+pp0ZK42BQ3ylgwbcA3K3GLPUMEKm4YN/EpKdgUCAiOWkqBE3C+4ApAJ9W1Q1OwQsOAPgv4JT3yBYzajJF/E0At3hJEr9I1crCsalgXMqMNnJ9xEybcJYRuAvARwAeAPDPM5FBUxT1LwH4CoAVI+x7EPWoj+xovw3gRwAOjUJx9xv8lsXQahHcIYKbFFhaSUZmAG1QObnGQmJS6dKM+lJcTJ23D8CfARzuuqhRSpCZ4HsAXBX6NAvXwYNJmvsz5f0Z6qH/AHi561i9EczXmDFnIq7+syy6mtv9trYh9BACJmUYtY5MunQU6xuFBE1Saj6BkDmJ9Qnbcoq5UNqauS8ycLxHmsTI37yGxmKPdPS2RyFBxpSbAVw7/P6PnZbTil7ddU6jkKCl9Go/JgHDc8q/cIMa9VFmKaUwJQ/lJDMQqhmGNBdxjccXsJ6IukqQi85FsEHEmfl6fy26RRazt8PpqUnGgJsYEC+aujJoGXfpLBT6IdQjyUIKpWqMEYkbi2dTfmoThJJ63nXXoUe/7Nwux6wrg4wxa1QxE3rJCAVHqwVEjMkdk4ZlSIYZvj9xn/LZ8Jr/rFBgrfllGl8e+tOVQSuI60x27GdctIQStGyx/fcXpQcqMr2zROtgV/1YSOTxFk1ImqJYK+cqSNCmySCSdup15JIuxqirBE2QSaNwF8ZBxfxqGzgs9TuGABYMnmgFg1vAr2wgL0l74Rq0SXqBSQcSJeH8OgSt/Y5BkvkXR9smIKgD6qUlC45Pa/AaODgRoyS5sW4ATjjw3/+/KOp6NA4RXjjWsZ9x0RHO78hi+++qpN+3CWixQzllmpMNyRyyNknWvAtQk876+B+qOkD/g2EXlFJXCTIGWRrmQMd+xkHzLqcGvNYFgu0ai9nRelX8JGxLy73O4mWpzhik/zKuQeQCpG3V95MKvGeYkIi8t5iFFdSrMBUpV7hAvX1AgT0KvJ5T1oUyTqk2jmQ+A0Iv5b9wLNJHTCq+zO+DyWHmSkvoP/Z9FP7LYcKbz3SJmkdMduT/CODvXbsdikFhEJr5HCdznlPFfCkZYRAaSFKUzZAqPmPs1Cy+w0Tx1eRMN+42CVI0/NP404lBA8gs2KvMlz/TxaSOiMys/x7AX0YB2i+MQZpTHuVfLwmwU3xGoZ0CK19214AbpRIJmnfJiJP6zOTTAH7JfH1ETc+10aKtWOmDVOL5qgIPQh1ovgHiMgyVwdKYMREHmtLRyN9fKeY07ar7yJwneMzi7hbh840yyLSQ40UADwH4LVMvp4pOcuwHAfx6lOnn7ph0HbP4E4uljO5sfCb1YTLBbNhQS1sH0iviQp5HoLhfi7Rzki1ZLI0jN3+AlRbK71b4tHkM44BS+yyARwH88Myv7qi23oLuh82TVeDrzDJcUMtqpB5wcj3MleViRhG8AOD7gDzgjpU05bQXTwth0DTzTdPJc7kpnaTTaOb/O86rFeyAusKnTeniS24kyUANykBKpilOqJea3SKuwuwpStJ5hH5bkSO6JUcZwA70stsYZEjcLNPKaziBswlhTocoae5h9ZN9mf6RVYG9pOIKC7bT0i2lZC0ZYCxO0J855os3XZ+PUBm/yRrGzxIbH4T79NiPMedd+PBoP1XBkVwk0MYgyytZdvLTIq5qwsDvKeLPPQ2R4rxY91inc6FAdpp/onBm+FciruLsMgWuFl+24lyC0lqX3ToRs4U8LoKXTMco8A8Ab0DwjqrTb/eK6DZmWFpxN8Jt807CxTHqDaib46MsCn1tGAbNMWX7BZbRXd8hK7CBi99HM3yQ33czG3sVd/RGSlJ4POyYvkdp+R6f2xuY7x4NwL0Azlnk/LYSstnI6pTfUemXCEDKoFWuTFfxNfUVW5MF4F3DuCTv9yWk1DmbknYT8b+ZhKnf+fMEuER8rXTR3yEy8icAfsOdnw9YuA7iFnZ2BImknriG4F11Xwlji9v87apOEGxD73euih8/YpBN7mZRfEP9jnpPeBATsnhP1SJON6jlydeLP/PHysX6nTKXYK0q5sQvuuhznwh+DHFx1fFkGMvHbVG/oF52chnXXOoIZhF79Jii/jx50uORO1Iox6UUdyuA+mJNZBvswpDYkbDKdVtDntyOzeNJ7KTUX4/lYiorlHD6UV3V/sInJY23nccSwnv4FsBkjwtYodC7FLoj13cWadD4uDUy0UMe66GufuisbB+KwxB8oBWCaNbkoKrDlOuVHT6dfB0XNDBtnQJrjYiKh14sjX6HKm41vWgMupiV65/jzoyD1rLK/sKmuWWSe72M+Re6H5fyXY9xFYoWVWpb+uKP1sVaHCsrCCjwhVS5wRcMoIiDwnLe9uTepDpzj/VU/CeSbq3oM5SuKUrb0nJcP1Kf7sYGha5uzUC2IQYt0h6QSee1puCuhOIKN8GCJ6nINhzYVvQgQA25ujlVbFXFZlVfXSsVojgL/wk7XSaCybIixPc3S8m5HIpeK8goSZDbcL2FTGCuMtDepOfCYSs00vOM4ZX1JMv0bqDPE05zNjN+4bGH45pFvIVWcfQUM83mdFGP53h5oQMqrLbCosulhBsgFeZcdCxowZVNGoAt4h2yKTLWbp+xD9SnwUWlqPuZVmBaY9Gfo7SvTfqOj9aQpjXEx92Y8SLdMe9xp2ZOQYVGj47olaxKK5TyLD31VElPceN6wX2XsvZwZsxzBfXdTK8EakMGpuczyEiUQXdashveK+Gf1T9nxgVrRLBFzINVzEMx5XSLuLcSwwzEBNQp7wky6QKGA8tcr1LP4TVlTsK5+bUIUvdB+UCgg11Dq9SEJXOly6J1dC9iWsZvKTvwtAaKmwKTP+UUcshXP1ifkmW8mBfgcgFuVMFyTY/TgGOVlv41Ua6LXpI0ylND+zAYeNS5X4jVDW5TOy5eZmcygSr4nqtJ1hTv2crjOYWGyYa5u0FzarJiqU/arzkOgmxNTw4nrvcqURCb9kGaYZBpR2ZC/FmfUA2SDwydCK3Mej9K1nuDQqkPX2YJl5Dxg2ozyAS1YVuYBTlVL/WG1OPR2UyYYa5FGCfo3a+hx7/oUrrFUr8uVLYjSVvO4cqacq1g1EhRZ/s4H4pr1OM7vUBsQx036bx8xWWAriqGKIdvyYhkpSo3/4x7EE6zlKAhsJ1mWmBCjrebg3ozUbzZgI/FHCYJ8W7lizLnpH3k1pg0WkjzCtTBtNZyBePCodfbz1Z7pTQg1imbGtrK3Y6V01qHVopb+Fy5WZUOm1V/DO1YXVYEpporp6nPSYkI7BXgSfXYDhiQ3wJxlrTftLVhY3nEYsbXlXRbLxGWLHFbNOX42gqIA7xW0tRPJrctZxbEO5jemsVzTYJkqeCSwyx9eQLi3qt/k1Dtd/mi3d0qPnCWYOHF13Dup0NJFzTFTMQqrjmdi5n+9fzepEma6CCB+OeJCmxmiucdVr2+T2RzYPxZTSpSfgUeybZU4Rbtdf2ep7ZjOVyII7ljXjYFc6fGOEqk0ph8m3hmHCOEYaiC+VfzSKSxlMBgkV3rpEdB4cls4VDAC010Wn0N05S+PcSWTzCGu5Ee/JFhT0/9psxMJTeHQSBVcSnd7eZbq2sdNo3PrqRCPiwe9D/Awqp17prPZPTCuZeliUGbeCvWMvOMt9w69ywn87chYUQWjQwYr1JrahtrKYE1s4RrmVt7VrwOsrLl28V78qUO0nDuAaNOp5IeJ52kzrmE/pMt/xe0brupm3YERqCR+jmfQhNOquSlI5eQq13LAW6DXK9CWkLlmxkj249vOyi+XnKVegzpWlrNGZdOUuxmTm29hJKZrEeDJFlsSTL+ekO4wNuH8Jtql+pcKMdI7wx9ruYRQjrEqo8P4TOm8wxn3mZWdwaQqahHTTIQNAN9imNUribJlwVGEvFCGqJriR277MMNwXkrFR6Keqa8yAqOJWx7l8HvjUQ1Y72skS60S/P2vpj3Ecrtl0CAMpo540HHUElyTRJJyXEBseSEv0nmrYtUK28xHME1c0C/zJz/LpYEW4JiK9/xvwGic1Ef9bUag4/3WfA4dQZVyY+CljLIXU1c+zgXvJ0MWj3EGOZxv9UXldccYAX11Va1nW4w34NkvjyeQQQZHbMwDZL0me5mUFrX6j/V1cE5UNwJcdbsKK3ayhAULMaKVIL/w5zJ1/s0e+ZI3dq63mF+im/IiKnJXVqwrmtTUL5tkrHY9ZSi9tirKIvx/R5Qxa6+iv7VaX11DpSlhyfK/of0luvzCv5u0F9SsDrUkg1ed5YaQLDU4Qz2bDLXWJuev3bSFWwJnjYJeotm/lHixJtys9SaQ6ONYUTcGLrD/JbziQZIleY2qFly4tfHc9fSV8olMBT+hy2fhODfRVHjfhYyPUylffpD2NND86xL+inDkwO+Oksdk6zm+GGib4dSCa4yi1p6dRpIawoYVlTdX3yiV5+Sy1EbnSVVidrKNHE6qIQ5sGKwoM94QdH9vOUDVuU+5HworWIxpVl7isdtP03ihrCAIO3/TBGzji8mg2vfSzVjP1q5i221YNWQuJ2qrgB8P8RBBte4vJSWWdjI0c5tTm0B/L+McQahBNGCWxRNKzXfY7UZZOq8+tNjlnwnxB2tF8J7m6L5Pfw9wl0sCd4IcQXbBX48kRbJB4NnGlMXIPDWWxycXNyXC4wjS5RIUyDpxRWzUEfVC8MrhD+e45prv7/YxKCPGMfYA9aJpUvsuBl0UPxYyFh+XHaMVGisonLfAlcrTjeJsXXakYq3BMD/AF5CfLZK99QUAAAAAElFTkSuQmCC"
                                class="" alt="icon" />
                            <p class="font-weight-light">{{ $project->client }}</p>
                        </li>
                        <li class="item-option d-flex align-items-center">
                            <img src="data:image/png;base64,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"
                                class="" alt="icon" />
                            <p class="font-weight-light">
                                {{ optional($project->employeeResponsible)->getEmployeeName() }}</p>
                        </li>
                        <li class="item-option d-flex align-items-center">
                            <img src="data:image/png;base64,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"
                                class="" alt="icon" />
                            <?php $teams = $project->teams; ?>
                            <p class="font-weight-light">
                                @if (!empty($teams) && count($teams))
                                    @foreach ($teams as $index => $item)
                                        {{ $item->getEmployeeName() }}{{ $index == count($teams) - 1 ? '' : ', ' }}
                                    @endforeach
                                @endif
                            </p>
                        </li>
                    </ul>
                </div>
                <div class="break-line"></div>
                <?php $tasks = $project->tasks; ?>
                @if (!empty($tasks) && count($tasks))
                    <div class="task-content">
                        <ul class="d-flex task-list">
                            @foreach ($tasks as $task)
                                <?php
                                switch ($task->getProjectTaskStatus()) {
                                    case 'DONE':
                                        $color = 'success';
                                        break;
                                    case 'NOT_DONE':
                                        $color = 'danger';
                                        break;
                                    case 'WAITING':
                                        $color = 'info';
                                        break;
                                    default:
                                        $color = 'warning';
                                }
                                ?>
                                <li class="box-item d-flex align-items-start">
                                    <div class="box-color box-{{ $color }}"></div>
                                    <div class="task-des">
                                        <p class="mb-2">{{ $task->title }}</p>
                                        <p class="mb-2">{{ Date('d/m/Y', strtotime($task->start_date)) }} -
                                            {{ $task->end_date ? Date('d/m/Y', strtotime($task->end_date)) : Date('d/m/Y', strtotime($project->end_date)) }}</p>
                                        <p class="task-comment">{{ $task->comment }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="created">Created on {{ Date('d/m/Y', strtotime($project->created_at)) }}</div>
            </div>
        </main>
        <!-- end main -->
        <footer class="container">
            <ul class="d-flex">
                <li class="box-item d-flex align-items-center">
                    <div class="box-color box-success"></div>
                    <span>done in time</span>
                </li>
                <li class="box-item d-flex align-items-center">
                    <div class="box-color box-danger"></div>
                    <span>not done in time</span>
                </li>
                <li class="box-item d-flex align-items-center">
                    <div class="box-color box-warning"></div>
                    <span>in progress</span>
                </li>
                <li class="box-item d-flex align-items-center">
                    <div class="box-color box-info"></div>
                    <span>waiting to start</span>
                </li>
            </ul>
        </footer>
        <div class="footer-logo">
            <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAZgAAADVCAIAAADl6HEEAAAACXBIWXMAAC4jAAAuIwF4pT92AAATtWlUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS42LWMxNDUgNzkuMTYzNDk5LCAyMDE4LzA4LzEzLTE2OjQwOjIyICAgICAgICAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLyIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0RXZ0PSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VFdmVudCMiIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczpwaG90b3Nob3A9Imh0dHA6Ly9ucy5hZG9iZS5jb20vcGhvdG9zaG9wLzEuMC8iIHhtbG5zOnRpZmY9Imh0dHA6Ly9ucy5hZG9iZS5jb20vdGlmZi8xLjAvIiB4bWxuczpleGlmPSJodHRwOi8vbnMuYWRvYmUuY29tL2V4aWYvMS4wLyIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ0MgMjAxOSAoTWFjaW50b3NoKSIgeG1wOkNyZWF0ZURhdGU9IjIwMjAtMDgtMjJUMTI6MzY6MjUrMTA6MDAiIHhtcDpNZXRhZGF0YURhdGU9IjIwMjAtMTAtMjJUMTM6Mzk6NDArMDc6MDAiIHhtcDpNb2RpZnlEYXRlPSIyMDIwLTEwLTIyVDEzOjM5OjQwKzA3OjAwIiBkYzpmb3JtYXQ9ImltYWdlL3BuZyIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDo1YjVhMGZkMy04MjNiLTlhNDUtOTU5Yy0xYzZhOTg4ZmY5MzciIHhtcE1NOkRvY3VtZW50SUQ9ImFkb2JlOmRvY2lkOnBob3Rvc2hvcDpjNDQ2Yzg1ZC00YjRhLWU1NDAtOTg5NS05YjZlODY0MzA1OGQiIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDphZDZlNWI0Ni04MDNjLTQ4N2UtODJiYy0wMDBiNDVhNGFhYTgiIHBob3Rvc2hvcDpDb2xvck1vZGU9IjMiIHBob3Rvc2hvcDpJQ0NQcm9maWxlPSJzUkdCIElFQzYxOTY2LTIuMSIgdGlmZjpPcmllbnRhdGlvbj0iMSIgdGlmZjpYUmVzb2x1dGlvbj0iMzAwMDAwMC8xMDAwMCIgdGlmZjpZUmVzb2x1dGlvbj0iMzAwMDAwMC8xMDAwMCIgdGlmZjpSZXNvbHV0aW9uVW5pdD0iMiIgZXhpZjpDb2xvclNwYWNlPSIxIiBleGlmOlBpeGVsWERpbWVuc2lvbj0iMjQ4MCIgZXhpZjpQaXhlbFlEaW1lbnNpb249IjM1MDgiPiA8eG1wTU06SGlzdG9yeT4gPHJkZjpTZXE+IDxyZGY6bGkgc3RFdnQ6YWN0aW9uPSJjcmVhdGVkIiBzdEV2dDppbnN0YW5jZUlEPSJ4bXAuaWlkOmFkNmU1YjQ2LTgwM2MtNDg3ZS04MmJjLTAwMGI0NWE0YWFhOCIgc3RFdnQ6d2hlbj0iMjAyMC0wOC0yMlQxMjozNjoyNSsxMDowMCIgc3RFdnQ6c29mdHdhcmVBZ2VudD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTkgKE1hY2ludG9zaCkiLz4gPHJkZjpsaSBzdEV2dDphY3Rpb249InNhdmVkIiBzdEV2dDppbnN0YW5jZUlEPSJ4bXAuaWlkOjUyM2JhNTAyLTU5ZGItNDE0Ni1hNzE0LTdiYmE5YzRlYzRmMCIgc3RFdnQ6d2hlbj0iMjAyMC0wOC0yMlQxMzoyMDo0MisxMDowMCIgc3RFdnQ6c29mdHdhcmVBZ2VudD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTkgKE1hY2ludG9zaCkiIHN0RXZ0OmNoYW5nZWQ9Ii8iLz4gPHJkZjpsaSBzdEV2dDphY3Rpb249InNhdmVkIiBzdEV2dDppbnN0YW5jZUlEPSJ4bXAuaWlkOjk3YmRmMzEwLTA2YjEtNGE0NS1iZGNlLTIyODhmMTJjZmQ4NyIgc3RFdnQ6d2hlbj0iMjAyMC0xMC0yMlQxMzozOTo0MCswNzowMCIgc3RFdnQ6c29mdHdhcmVBZ2VudD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTkgKFdpbmRvd3MpIiBzdEV2dDpjaGFuZ2VkPSIvIi8+IDxyZGY6bGkgc3RFdnQ6YWN0aW9uPSJjb252ZXJ0ZWQiIHN0RXZ0OnBhcmFtZXRlcnM9ImZyb20gYXBwbGljYXRpb24vdm5kLmFkb2JlLnBob3Rvc2hvcCB0byBpbWFnZS9wbmciLz4gPHJkZjpsaSBzdEV2dDphY3Rpb249ImRlcml2ZWQiIHN0RXZ0OnBhcmFtZXRlcnM9ImNvbnZlcnRlZCBmcm9tIGFwcGxpY2F0aW9uL3ZuZC5hZG9iZS5waG90b3Nob3AgdG8gaW1hZ2UvcG5nIi8+IDxyZGY6bGkgc3RFdnQ6YWN0aW9uPSJzYXZlZCIgc3RFdnQ6aW5zdGFuY2VJRD0ieG1wLmlpZDo1YjVhMGZkMy04MjNiLTlhNDUtOTU5Yy0xYzZhOTg4ZmY5MzciIHN0RXZ0OndoZW49IjIwMjAtMTAtMjJUMTM6Mzk6NDArMDc6MDAiIHN0RXZ0OnNvZnR3YXJlQWdlbnQ9IkFkb2JlIFBob3Rvc2hvcCBDQyAyMDE5IChXaW5kb3dzKSIgc3RFdnQ6Y2hhbmdlZD0iLyIvPiA8L3JkZjpTZXE+IDwveG1wTU06SGlzdG9yeT4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6OTdiZGYzMTAtMDZiMS00YTQ1LWJkY2UtMjI4OGYxMmNmZDg3IiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOmFkNmU1YjQ2LTgwM2MtNDg3ZS04MmJjLTAwMGI0NWE0YWFhOCIgc3RSZWY6b3JpZ2luYWxEb2N1bWVudElEPSJ4bXAuZGlkOmFkNmU1YjQ2LTgwM2MtNDg3ZS04MmJjLTAwMGI0NWE0YWFhOCIvPiA8cGhvdG9zaG9wOlRleHRMYXllcnM+IDxyZGY6QmFnPiA8cmRmOmxpIHBob3Rvc2hvcDpMYXllck5hbWU9IlNjb3BlIiBwaG90b3Nob3A6TGF5ZXJUZXh0PSJTY29wZSIvPiA8cmRmOmxpIHBob3Rvc2hvcDpMYXllck5hbWU9IlN0YXJ0IERhdGUgdG8gRW5kIERhdGUiIHBob3Rvc2hvcDpMYXllclRleHQ9IlN0YXJ0IERhdGUgdG8gRW5kIERhdGUiLz4gPHJkZjpsaSBwaG90b3Nob3A6TGF5ZXJOYW1lPSJDbGllbnQiIHBob3Rvc2hvcDpMYXllclRleHQ9IkNsaWVudCIvPiA8cmRmOmxpIHBob3Rvc2hvcDpMYXllck5hbWU9IlByb2plY3QgTWFuYWdlciIgcGhvdG9zaG9wOkxheWVyVGV4dD0iUHJvamVjdCBNYW5hZ2VyIi8+IDxyZGY6bGkgcGhvdG9zaG9wOkxheWVyTmFtZT0iVGVhbSIgcGhvdG9zaG9wOkxheWVyVGV4dD0iVGVhbSIvPiA8cmRmOmxpIHBob3Rvc2hvcDpMYXllck5hbWU9IuKAnFByb2plY3QgTmFtZeKAnSBSZXBvcnQiIHBob3Rvc2hvcDpMYXllclRleHQ9IuKAnFByb2plY3QgTmFtZeKAnSBSZXBvcnQiLz4gPHJkZjpsaSBwaG90b3Nob3A6TGF5ZXJOYW1lPSJDb21wYW55IE5hbWUiIHBob3Rvc2hvcDpMYXllclRleHQ9IkNvbXBhbnkgTmFtZSIvPiA8cmRmOmxpIHBob3Rvc2hvcDpMYXllck5hbWU9IkJVU0lORVNTIiBwaG90b3Nob3A6TGF5ZXJUZXh0PSJCVVNJTkVTUyIvPiA8cmRmOmxpIHBob3Rvc2hvcDpMYXllck5hbWU9ImNvbXBhbnkgbG9nbyIgcGhvdG9zaG9wOkxheWVyVGV4dD0iY29tcGFueSBsb2dvIi8+IDxyZGY6bGkgcGhvdG9zaG9wOkxheWVyTmFtZT0iZG9uZSBpbiB0aW1lIiBwaG90b3Nob3A6TGF5ZXJUZXh0PSJkb25lIGluIHRpbWUiLz4gPHJkZjpsaSBwaG90b3Nob3A6TGF5ZXJOYW1lPSJub3QgZG9uZSBpbiB0aW1lIiBwaG90b3Nob3A6TGF5ZXJUZXh0PSJub3QgZG9uZSBpbiB0aW1lIi8+IDxyZGY6bGkgcGhvdG9zaG9wOkxheWVyTmFtZT0iaW4gcHJvZ3Jlc3MiIHBob3Rvc2hvcDpMYXllclRleHQ9ImluIHByb2dyZXNzIi8+IDxyZGY6bGkgcGhvdG9zaG9wOkxheWVyTmFtZT0id2FpdGluZyB0byBzdGFydCIgcGhvdG9zaG9wOkxheWVyVGV4dD0id2FpdGluZyB0byBzdGFydCIvPiA8cmRmOmxpIHBob3Rvc2hvcDpMYXllck5hbWU9Ik1hcmtldGluZyBSZXNlYXJjaCIgcGhvdG9zaG9wOkxheWVyVGV4dD0iTWFya2V0aW5nIFJlc2VhcmNoIi8+IDxyZGY6bGkgcGhvdG9zaG9wOkxheWVyTmFtZT0iMDEvMDkvMjAxOCAtIDA5LzA5LzIwMTgiIHBob3Rvc2hvcDpMYXllclRleHQ9IjAxLzA5LzIwMTggLSAwOS8wOS8yMDE4Ii8+IDxyZGY6bGkgcGhvdG9zaG9wOkxheWVyTmFtZT0iRGVzaWduIHRoZSBQcm9kdWN0IiBwaG90b3Nob3A6TGF5ZXJUZXh0PSJEZXNpZ24gdGhlIFByb2R1Y3QiLz4gPHJkZjpsaSBwaG90b3Nob3A6TGF5ZXJOYW1lPSIxMC8wOS8yMDE4IC0gMTIvMDkvMjAxOCIgcGhvdG9zaG9wOkxheWVyVGV4dD0iMTAvMDkvMjAxOCAtIDEyLzA5LzIwMTgiLz4gPHJkZjpsaSBwaG90b3Nob3A6TGF5ZXJOYW1lPSJCdWlsZCB0aGUgUHJvZHVjdCIgcGhvdG9zaG9wOkxheWVyVGV4dD0iQnVpbGQgdGhlIFByb2R1Y3QiLz4gPHJkZjpsaSBwaG90b3Nob3A6TGF5ZXJOYW1lPSIxMy8wOS8yMDE4IC0gMjIvMDkvMjAxOCIgcGhvdG9zaG9wOkxheWVyVGV4dD0iMTMvMDkvMjAxOCAtIDIyLzA5LzIwMTgiLz4gPHJkZjpsaSBwaG90b3Nob3A6TGF5ZXJOYW1lPSJUZXN0IHRoZSBQcm9kdWN0IiBwaG90b3Nob3A6TGF5ZXJUZXh0PSJUZXN0IHRoZSBQcm9kdWN0Ii8+IDxyZGY6bGkgcGhvdG9zaG9wOkxheWVyTmFtZT0iMjMvMDkvMjAxOCAtIDI1LzA5LzIwMTgiIHBob3Rvc2hvcDpMYXllclRleHQ9IjIzLzA5LzIwMTggLSAyNS8wOS8yMDE4Ii8+IDxyZGY6bGkgcGhvdG9zaG9wOkxheWVyTmFtZT0iQ29tbWVudHMiIHBob3Rvc2hvcDpMYXllclRleHQ9IkNvbW1lbnRzIi8+IDxyZGY6bGkgcGhvdG9zaG9wOkxheWVyTmFtZT0iTGF1bmNoIFByb2R1Y3QiIHBob3Rvc2hvcDpMYXllclRleHQ9IkxhdW5jaCBQcm9kdWN0Ii8+IDxyZGY6bGkgcGhvdG9zaG9wOkxheWVyTmFtZT0iMjYvMDkvMjAxOCAtIDI3LzA5LzIwMTgiIHBob3Rvc2hvcDpMYXllclRleHQ9IjI2LzA5LzIwMTggLSAyNy8wOS8yMDE4Ii8+IDxyZGY6bGkgcGhvdG9zaG9wOkxheWVyTmFtZT0iQ29tbWVudHMiIHBob3Rvc2hvcDpMYXllclRleHQ9IkNvbW1lbnRzIi8+IDxyZGY6bGkgcGhvdG9zaG9wOkxheWVyTmFtZT0iQ3JlYXRlZCBvbiB4eC94eC94eHh4IiBwaG90b3Nob3A6TGF5ZXJUZXh0PSJDcmVhdGVkIG9uIHh4L3h4L3h4eHgiLz4gPC9yZGY6QmFnPiA8L3Bob3Rvc2hvcDpUZXh0TGF5ZXJzPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PuKXdrsAAB/KSURBVHic7d1rc+LIuQfwbrUuCCEjwMbDGNsbp7KVfP8vklN1TuXUps7u4MHGBgsEAgndz4ueJYyNzYOQBDLPr/IiOyND02P96W71hSZJQmAcx5nP547reJ4XBEEcx/CfRQih/IhbrwiC4MV8mUwmQRAUUCCEENrVR0EWRuHz07M5NrHlhRA6Zu8GmWVZD48PYRgWWRqEEEphQ5AlSfLw+GCaZvGlQQihFF4HWRzHvfvebDY7SGkQQigFYf0/kiTBFEMIlc5PQfbw+IAphhAqnf8EmWVZOC6GECqjH0EWRuHD48Nhi4IQQun8CLKnpyecaYEQKimBEOIH/ng8PnRJEEIoJYEQYpo4dx8hVGICIWQymRy6GAghlJ7gOA6uBkcIlZown88PXQaEENqL4LjOocuAEEJ7ETzPO3QZEEJoL0IY4PQxhFC5CVEcHboMCCG0FwFnkCGEyk7YfglCCB03DDKEUOlhkCGESg+DDCFUehhkCKHSwyBDCJUeBhlCqPQwyBBCpYdBhhAqPQwyhFDpYZAhhEoPgwwhVHoYZAih0sMgQwiVHgYZQqj0MMgQQqWHQYYQKj0MMoRQ6WGQIYRKD4MMIVR6GGQIodLDIEMIlR4GGUKo9DDIEEKlh0GGECo9DDKEUOmJhy4A+lQopYIgMMbEFSYKgkAoIYQkSZLESRRFYRgGYRCGYRRFcRwnSXLogqNywyDbTJIkURQFQRDoj5uQJCRJkjiOwyj0fb/g8lBKFVkRmCAIAqW8QD/KE8ex7/txHBdcpPWyybIsy3KlUlEURZEVSZKYyJjAVkV9JUmSKIqiKArCwPd9b+ktvaXneUEQFPlBZFkWRVGgAhV+qtIoinzfLzheJUlijDGBUYHyekuShFdUGIRhFBZZmNLBIHtNFEXDMAzDUGRlPTUI/y1P4sAPZrPZy8tLYb9b1Wq1fdFWVZUxJgg/jQbwu8513dFotHAWxZRnpVKpaJpW02oVtaLIynux9RallDfXFEUhGiGEJEniB/7SXc4X88Vi4bpujuUmRJbl9kVb0zRJkiil698NSZKEUbh0ly8vL/PFPNdicKIonp+f12o1WZIZY69+5aIoWnpLa2JNrAk2Xd+DQfbaZfvy/Px8419RSgUiiKqoqmqcxMPhsIDyiKJ49fWqWq1u/FvGGGNMlmVZkf/4448gCAooEiHk7OzMqBuapsmynMkL8ianIiv1et33fcdxrKk1m81yunUvzi9ardbGYhBCGGOKrFTUyh9//OF5Xh4FWHfeOr9sX278K95VlySpptXiJLYsK+/ClBQG2WuVSgV0mVKhlBbwDSlJEiQsZEmWJKmAINN1/bx1rmkaYyynt+Ad1bOzM8dxXl5eZnbGccb7wlsvU2RFVdW8g0wQBFVVt15GKdV1HYPsPRhkr8UJaIymyEY+5L0KKI9aUc8vzo268ap7mxNBEGq1mqZp0+n0efi8XC4zfPEojiCXSaKU4ZtulCQJ8OuHd96xd7kRBhnaThCERqNx2b6UpNxv7FcopYZhaJo2HA7NsZnJbZwkSRTCgiz/z5skydIDZTR/NFHY6EG5YJChLWRZ/vLlS8NoHLAMkiRdXV2pqjp4GoRhBs9YgHEgimIBjSDf85Mk2fqohA+GYpBthBNi0Ue0qvbL7S+HTbGVZrN5c32TyeMF4BNnJr5+TJyHIAgg8UQpVRQl78KUFAYZeletVru5vYEMRRdG1/WbmxtF3vd+jiJQ11JkInxOSWpBGAQhKMiyekb8+WCQoc1qtdrN9Y0sHd2do1W1bre75+gVcDnBq4mEOeFTXiFXYpC9B8fI0Aaqqu4TFmEYvlqERBJCKOGrlyRRWq1fSvf6tVrt6uvV/ff71MsA4iiGDEsJglBA1zJJEs8HTfKQREkQhAOu4jhaGGToNUmSrrvXKbpvYRguFov5Yr50l57vhWG4sdXDp/UrilJRKlpN06paisSs1+uX3uXgabDrD3JRHB1Pi4wQwldEbX0vvowJg+wtDDL0E0ppp9PZdVwsDENzbE6n0+VyuTUg+MypIAjm87k5NiuVimEYzUZz1wbaxcXFwlnMZrOdforja1S3zuldX72UK75admt5GGPFTHsuHQwy9JNWq7XrM0rLsobDobtMszoySRLXdV3XnVrTi4sLwzDgP0spvby8dF03xY3N11RC3qKYIAv8IIqirUHGlysVUJ7SwcF+9B9qRW1ftOHXR1H08PBw//0+XYqtc1zn/vv94+AR+DyRq6rVZrOZ7h2PK8jCAPLBKaUFLDYoIwwy9AOltN1uw7/wgyDo3fdezJes5osmSTIajb5//77TlNdmswlcHpsOJUUEGd+jbXthKBUl7EVtgEGGftB1/ezsDHhxEAT33+9t2868GNPZ9Hv/O7xdJktyukZZQmD5W0SOEUIIcJM7nIGxEQYZIoQQxlir2QJONYii6HHwOJ/ntVfXbDZ7enqCN/QaRqOi5NgoKwZwpI/PwMi7MKWDNYIIIUTTNF3XgRebppn3fjLm2JxYE+DFoijW6/WcSlLYbhPvzVZ5he9AV0B5ygWDDP3YYQI4qu04znCU+46SSZI8Pz/7AXRLccPYeXOhYga/4IDLP/mk4rwLUzoYZIgoinKmg0bHkiR5Hj7v9GAxNd/3X15egBcrilKr1TIvQ5Gbf4UBuEUmYJC9hkGGSP2sDvySn81meQzwv8eyLOB+ipRSYBav/wjksuK6llEImbIvCAITMchewyA7dYIgAB9WJkmS4WQLiCAI4CNlmqbB1wYA+5VFflh+jszWy7BruREG2alTKypwQdJ8PnccJ+/yvGLbNvRxniTtMKGMglpkCQEtAMgEMMgIIRhkb2GQnbqaXgN2smazWfHLlZfLJfBoOMYYfIkodMp+UlyjLI5j4EkCGGRvYZCdOq2qQS4Lw7D4czMJIUmSLBbQ9808yJIEOm12f0mSxBHoewIH+9/CIDtpsiwDd092XbeAEx43clwHODVBkRXgMJlAQb/5/EhmyJWZgG7AjS2yNzDITpqiQO9813UPtQ3WcrkEbqAqiiJwrSgVQC0y4EayWcExstQwyE4aP2Z462VxHO+/v0VqYRgCN1CF7zoL71oWGmSwQ+pwidJbWCMnTVEUyP0cRdGh+pWctwS9O3y7LmAWFNmvJOBjgzHI3sIaOWnArRSCEHReWX483wO2jKBBRkF7WPOt/SEvmAlg550Kx7W46hhgkJ0ueEcsDMJiliW9J/ADYKBAu5awLCh4jCyJQe8FfFJxUrBGTpcoisBh4yCA5khOgjAAtlaAJ1ECs6DgrmXBb/eZYJCdLsagx2hDjo/NVRzFwCADnnsEbJElcaGD/cD3Kmb37XLBIDtdwHs+SZLD9ivJLst3BEGAtLaOc7AffiRKAYUpFwyy0wVvkQFnnOcnTqAtMkpB68GhE2Jhg1ZZOWz/vdQwyE4X8MldwbPb9ywDcIIYMMELnkcGVNyyqfLAIDtd8OlIBTdMNhQg6zWP0KeWh07wzTDH3sAgO2GlGmnJtmUEX2uZ4ZtutcNSdvQzDLLTdWyb1hdmh2N3i02MY9u0tkQwyBDAESQe/CbP8MFfwaNR0GcvB1q9f8wwyE4X8C6llB58Kjm8DZVta6Xgtg+wnoFLMk8KBtnpgn+xC6w0QRbHoIkaxzkVC1jPwE0yTgoG2ekCTlunlB58S1L4iRsFr47MlshgS19D0O5sJwWD7HTB7/mD7+QnCAKwtRJGoNMhj5MoYZClhEF2uqI4gq7Eht1g+YGfSpvtTV7wg11JBO1BdPClr0cIg+x0wTfbkkTpsINKsiQDC5DtvmlFfmpBECB7EEVRdNi94Y4TBtnpCiPoLmPwnctyIsugIIPf5MAEL3IvVsYYZFfIMAyxa/kWBtnpCsMQ+CAffqhHTuBbcmcbZIyxwhplsixDvi3CMAQexXJSMMhSOs7n9zuJ4xh4SwAbCzkRBKGigo4QDwLQltxJkkB3Nytw3gnwIBjf94Gnxp0UDLKUgJt5HTng6USCIFQqoCjJgyzLsgQ6W8DzPWBnGXiZJEqF9S4rKijIDnic1THDIEtJYMUFWX4LZTwPeqiHWlEPFdyqqkL6XEmSuA70Jgc2akRJLCbIKKVVtbr1siiKlstlAeUpHQyylESxoF/xzHewWectPeAwmaqqigw6kzxzmqYBB8hcFxxksPFySZKKmUNXVauQE62CIMAg2wiDLCVJLOhXvKbV8hufWnrQQ7xlWVarak7F+Ph9taoGudLzPXi3KwxA82ZFJhYT35qmQVqd7tLFuRcbYZClJIoi8FDIfei6fnl5mV+fbqdWzJl+VvzRsJqmAYfnFosFfPVoEAbAYTJVzT2+BUGo1WqQKxfzRd6FKSkMsvQggxr7UCvq1dervCdwzRdz4JW6ritKob1LQRAaRgNyZZIktm3DXxk+G6uqVfMeHKyqVUhrNwxDe77DZzwpGGTpAcdu0pFl+fr6eqfgSLeeZjFfAEe+GWOtZivFW6RWq9WATRXXdReLHVorcRx7HuiJLXD0ah/1eh2yXHw+n/u+n2tJyguDLL1qtZpTv0OW5Zvrm51fPFWo+oEP77A0Go1qNd926Apj7LIN7VZblrXTWvE4joGj5oyx+lkd/sq7UlXVMIytlyVJYllWfsUoOwyy9ARBgPwK7kqSpG63q2mgEe4VSmm6AawkSaypBbxYEIQvl1+KmYfRarWAoRkEwXQ23fX14U8GDMPI6cEOpbTVbAGH+eGDACcIg2wvDaNRUbKcKcrbYnpN3/UHKaWpb7b5fA5/qK/rOryhlFq9Xv9y+QV48WQySdHn8jzPD0A/ValU8vjGIoTout5sNiFXTsaTgx+TfMwwyPYiiuLFxUVWr6YoynX3Gjgq9Apw74SNwjCEdFuiKFo4i8lkIghCriuWGGNnZ2dBEECeQgZBMJlMUryL7/vA+KaUnrfOM//IkiR1vnQgXwnL5TJFk/OkHHifqU/AMAx7bu8/fqFp2tXVlVpJP+gGXMezkTW1ms3mB6PaM3tmmqZt2wVsWxjH8cPDg8hEraY1G82Pk308Hi+9NHNE4zhezBdn+hnk4kqlctm+7D/0U7zRRoyxq69XwJklk8kEp499DFtk+xIE4Wvn665DWq80m83bm9t9UowQUqmAFutt5HneeDJ+729HL6P7+/vZbFbM5qt8Ubcf+JPJpNfrjUaj965cLpfm2Ez9RrZtwwOi2Wyen5+nfq91PMXqddAzBNd1P/inQRwGWQYkSbq5vtH1nQe2yJ9D+92r7v49l0qlss/S7vF4vLGrNZlMBoPBxgEa+Fb6qYVROHgamOaGtIrjeDgc7tNU2WkEnVLa+dI5b+2bZXxiTaMBmh8Xx/FoNMINyLbCrmU2ZFm+vbkdjoamaQIHZSmlDaNxcXGR1cYSkiQZhgGfqf9KEASj0ajb7a4363zff3p+etUQo5Tqut4wGqqqUkp5kyF1r5NSWqvVDMNQZCUMQ9u2ram1XodJkjw9P72d7DKxJhMrzejYTy8ymdTP6sAHvoIgfP36VVbk4XCYIlwopfV6/bJ9Cf8Xn06n8GfKpwyDLDOMsc6XTv2sbo7NuT0Po3DjWDUflddreqPRAHZIl8slcEew89a5t/Qm1mRjpoiiSCkNw3eXGY4n41qttt5YsCzr1QNBQRA6XzqtVmuVd7Isn52dzezZZDJxHOeD119HKRVFUVXVhtGo1+urV6vX64Zh9B/66xNWwzAcT8ZX6tXqTxzHeXp62vouW9m2bds2sJfHi31xflHTaqOXkW3bwDgTRbFarbaarbMz0JAc53ne228RtBEGWcaq1Wq1WvV933GcpbcM/CCKojiJ+aFqkixVKhWtqsEni3ue17vvXbYvITMABEG4urrSNG1mz/hTP0qpyERZliuVilpVRVF8enr64NHE4GlQUSt8tC5JkldrYiilXztfW63X8/sppfWzev2s7nnewlks3aXne2EYRlG0OvqbEkoFynNckZWKWtGqm9dR1mq1m+ubP779sR4Ttm1HUcR7skEQPDw8ZNXhGg6Huq7vNAtPVdWb6xvXdW3bdlzH9/0wDNdPpRKoIDCBMaYoiqqqNa2260TiOI4fHh9wKj8QBlkuZFleRdXqlzvFSLzv+9+/f18ul/B5XoIgNJvNRqMRRREPMsbY+l3abDQ/CLIgCPr9/i+3v0iSFEXRq7Co1+tvU2ydoiiKopAGSZIkiiJehh9BRikvDG8YfvwpqtXqxcXFYDBYL5jv+6qqhmH4vf/dcZ2PXwHOcZ3haAiftraiqqqqqkmShGEYBEEUR0n8Z5AJAv+k6YY+kyQZDAY7rR49cRhkuUv9JJGn2MJZEELsuX2Z7DANlXfcNv4Vz5EPOiyO4zw8Plx3r181UgRBgA918wLss+K92Wi+vLysj+VTSqMoenh8yPwOH41GWlVL97iGUipJUrazzIbD4Yv5kuELfnr41PJIeZ53//1+9UxtuVzyRNsfbxl9fM10Ou0/9Cml67tx/WhtFYUxVtP+M4OMn/nYf+jnseSQ9+OOZM/C4Wj4PHw+dClKBoPsGC2cRe++t76dQxzHmd3AFNRItCyr3++vz02TpOI2sCd/tnRW/ykw4fHxMb+F057n9R/6hx2TiuP46elpMBjgAP+uMMiOjmVZ9/f3b2dRTKfT1FMrfgK+R8aTsTW1VkG2GrYvzPokDNd1896Na7FY3H+/B27vk7kgCPoPfWyLpYNBlobne9lkys+iKHp6fvre/76xXRCG4fPwGb4J6nt2yiPP81bvuFwui1y3HARB8fsILhaLb71vxb+vbdvfet/SLRpFBIMsnSjMfsh5Pp9/6317fv4oqmaz2cvLvmPAfEpEih8MgmD/CahASZI8D58P0tFbLpe9Xu/p+amY1A6C4HHw2LvvOU5mz2FPED61TEOSpDAMe/e97lV3/w1ePN97eXmZTLbv08LnuFNK99lyw/OhR8C9NRwOJUlqGI1ct/EJw/D5+XnjsqRiRFH0/Pw8t+cX7Ysz/SynD8s3HTHH5pE8ZCg1DLI0qEAZY6sHi+etc0VRdv11j+PY8z1rYo0nY/jcziRJHgePnu+12+0U210kSTKbzXb9qZU4jvv9/mw2axiNarUKmRG2E74t/Wg0yqPnvquFs3B6jq7rzUYTeMoRRBzHfCfIyWSCEZYVDLI0BPrjdN4kSUzTnE6n9Xpdr+mVSmXro70oinzf50PXfLZ6igKYpjmfz1vNFj8QBJgmy+Xy5eVlnyAjhCRJMp1Op9NppVKpabWqVq1UKpIopb7P+Y3NlwTYtn0MEbbCc382m1WrVb2mazVNkRVJklLEN58067rufDGHr21CQBhkabzaVzoMQ9M0TdPkM60UWZEVWWSiwASBCuTPfWmCMPA8z/O85XK5//ZSnuc9Dh5lU65Wq1pVUxSFT0ClAuWnkKzeNAxD3/PdpbuYL4B7okL8WG9gEkmS+KdWFEWSJFEUGWP8s6/PWeMPGeI4juOYT/oPwsD3fM/3PM/zff+Y5xw4juM4Dh1RZQ0/25QvnHj7SaM/+b6/9Jb83x3zKycYZGnwhZNv/5zn1Ooa/vtN1u7hzEvi+77v+5Zlre6oH2+a/AiyKP6xTijzt14JgiAIgjmZkz933OZlEKjwI1UpIQlJSMKXK63iLNdS5SFJkvXlYusV/qORvv5J4ySKozJ+zDLCIEtJYFse+PLv5GIKQwjhX/6Fvd17+MLDQ5eiIEdS54jg9IvUNrbIEEIHgUGW0tYWGUKoMHg3plTkqkOE0MfwbkxpNQMDIXRwGGQpYYohdDzwqWVanzHHZFk26oZaVRVZ4Sd9uK7r+d7cnr86EGR/fL5IGbdyzryWiqz2z4r+87/+eegyHJe//OUvkENbx+Nx/6H/dg5nt9ttNTdsBu37vud7U2sKP4ex2+1W1epv//4NeP1bv/7tV8d1+v3tx8rKstzpdIy68d4FURRZU2s4HG6NHsbYX+/+uvV9//H3f8iy/K///deuWcYY45+r1+t9cNl7/xDvsef277///vE1GdZSTi94srBFlrH3bh6+i79e09vtdr/fh2wUw19KluV0v8SyLPNN5bcGmVE3ut0uY4zfNnN77rgObwgwxvSaXtNrRt1oNVtG3RgOh8PR8INXY4xB3pefafDL7S+7JjVjjFdmj3wUZDulGCGkqm45HCTbWsrjBU8ZBlkuer3eq9MxeK+h1WrJsnx3d9fr9Y7kvEKjbtze3hJChqPhcDh81ZGJosgcm+bYHMiDdrvdarY6nY5hGN963zJpI6iq2u12IW3GdN7+Q7zn4x5c5rV02Gr/fDDIcsGPCFv/E9/37bk9HA1vb2/5V/Hba4ony3K32yWE9Pv9j/u8vu/3+33TNK+716qq6jUd3kf+WKvZ4oNBmbzaK5lUcua1dAzV/slgkBWt1+uxO7bqYx62MO12mzHGv/wh17uu+9u/f9NrelZ7qNpzW6/pRxLr78m8lg5e7Z8PTr84gNFwRAj5YIi3MHpNJ4QMh7sNvmR4Ow0GA9d1GWO/3P6S1WtmLvNaOni1fz4YZAdgz38cmg0/bzwPqwIcsCkURdG33rcoilRV7XQ6hyrGBzKvpWOo9s8Hg+x0HcnmDb7v8xPF2xdt3lQ5KpnX0pFU+yeDQXYAsiwzxsgRfCfzJ3q7zlTInDk2+WD/7e0tr5mjknktHUm1fyYYZAfQbrfJcQx5jM0xIaTT6fAJ5QfU7/d932eM8UkJRyXzWjqeav80MMiK1mq2+FcxH/I/LGtqmWOTz8VvX7QPWBI+WEYI0Wv6YUvyVua1dDzV/mng9IviqKrabrf5w0pzbB5Di4wQ0u/3oyhqX7Q7nU6r1bKmlmmaB+nzuq47GAw6nU6n0yngXPGdZF5Lx1PtnwMGWS5+/duvr8Z6+GNK/v/5HXuIcm02GAwsy+p0Orw11L5o+77vuI7ruK7rrtbNFGA4Gtb0Gp9Z9tu/fzuqQfHMa+l4qv0TwCDLxdsR6/U/UVX117/9ClxxWQzXdX///XdVVQ3DMOoGX8y4mulmz+2pNS1mJ4Zer8fXk3e73Y+XhUO8/UZ5y/f9//v9/yCtocxr6XiqvewwyHIxGAzeuzH4SmC+4nLrCpWCua7LW4uyLFfVqizLvH3E/9fpdMyx+XZhYLaiKOr1end3d0bdmDfne9YP5Bno6iEyUOa1dAzVXnYYZLmwptZ7QWZNrcFg0O12+YpLvgaz4OJtxU+ZI4QMR0O+E0PdqBt1o33RbjVbvV4v1zLzRantizZfurTPkb3ARePpBqcyr6XDVnup4VPL1yhwy8Q9NlbkjQ4+c4ovHj5mfJOZXq/3r//9lzW1GGN3d3d5z1zlS5cIIXsuXeJLOLfav8CZ19JBqr28MMheixPQcapxtO+pq3zm1PqYyJHzfb/X6/HHFAXMXOVLl1YbRZRF5rVUcLWXFAbZa6PRyLa3NOAdxxmPx2+3h90J/8olhNSN+sYLeEtBkZV0r89/MPMn+sPRkDcQ8p6YznewIYTwnQVzfa/MZV5LhVV7SeEY2WuLxaJ336tWq6qqSpIkCALvbCYkieM4DMPlcuk4ThAE+7/X3J63L9rv7U3q+R7f5TXdyAifNe753l5F3GRsjo26YRhG3nuWWlOrNq61mq0j3+dno8xrqbBqLyMMsg2iKLJte2u7bH88Zd7bA2Nuz/l+x+l+cQ3D4C+yRwE348PnxSyv6ff7VbWqqmqKTbEPK/NaKrLaSwe7lof0ce+Pdzz1mp7id5fvmr96kWwVvOKdL1062n1+3pN5LR3JRgPHCYPskD7u/fm+z2dRXXevd31l/iPmOJdVL/zZGXAv/P2tBsuOc5+f92ReSwVXe7lgkB1Sq9UihEyt6XsX8GmQ/IQO+Mt2u11VVaMo+ngP0ru7Oz6HHv7KhBDGGG8Z8S0cirG+z0/qpx/pZF5LJar2EsEgO5hOpyPL8urZ5UbrT+6AWbY6z5FP7/jgSj654R9//wd8Awa+YQNjzJpaBU/OXO3zU/BsjMxrqVzVXhY42H8YnU6H/x7zXRA+uNKaWqzPeDxV1epgMHjvV9moG+12m3dX+/3+1tGxXq/nd/zVBgzD4fDj9UCrcxhd1y3+2JQoivr9/t3dXcH7g2deS+Wq9rLAIMvFezMq1k+3JHz/A8BgvDk2oyjiHca7uzu+xY3ruI7rMMYUWVGrKl+/Sf5cNgD83l7fgKHb7XY6Hb5Qef2kWEVWVutDCSH23O71egdZ92fPbb7PD/xHth67u/LBDtSZ11K5qr0UMMhysXWbU96+gD9S5H2KTqdj1I3VE8m3r5lidTHfgEGv6RftC72mG3XjvdmnrusOh8PDniu82ucHeD18v9koiv77f/77vb/NvJbKVe3HD4MsY+bY/GDuNd9wip9Hu+u3K8++wWBg1I2aXlNkhQ+xRVHk+V6611yx57Y9t2VZ5jPX+Iszxnzf93zPdV3LsoCLt6Mo4ttpfXyZOTarajXFQ9Ver/fXu79CXn+nSfCQp4EZ1lJOL3iy6D//65+HLgNCCO0Fn1oihEoPgwwhVHoYZAih0sMgQwiVHgYZQqj0MMgQQqWHQYYQKj0MMoRQ6WGQIYRKD4MMIVR6GGQIodLDIEMIlR4GGUKo9DDIEEKlh0GGECo9DDKEUOlhkCGESg+DDCFUehhkCKHSwyBDCJUeBhlCqPQwyBBCpYdBhhAqPQwyhFDpYZAhhEoPgwwhVHoYZAih0sMgQwiVHgYZQqj0MMgQQqWHQYYQKj0MMoRQ6WGQIYRKD4MMIVR6GGQIodLDIEMIlR4GGUKo9DDIEEKlh0GGECo9DDKEUOlhkCGESg+DDCFUehhkCKHSwyBDCJUeBhlCqPQwyBBCpYdBhhAqPQwyhFDpYZAhhErv/wHTq+YnIV3+XAAAAABJRU5ErkJggg=="
                alt="logo">
        </div>
    </div>
</body>

</html>
