<!DOCTYPE html>
<html>
<head>
    <title>Quiz Result</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background-color: #f5f5f5; }
        .correct { color: green; }
        .incorrect { color: red; }
    </style>
</head>
<body>
<div style="text-align: center;">
    <img src="{{ public_path('imgs/cug_logo_new1.png') }}" height="50" style="margin-bottom: 20px;">
</div>
<h2  style="text-align: center;">{{ $studentName }}</h2>
<h3  style="text-align: center;">{{ $attempt->quiz->title }}</h3>

<h4>Quiz Summary</h4>
<table>
    <thead>
        <tr>
            <th>Score (%)</th>
            <th>Earned Marks</th>
            <th>Total Marks</th>
            <th>Correct Answers</th>
            <th>Total Questions</th>
            <th>Grade</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $score }}%</td>
            <td>{{ $earnedMarks }}</td>
            <td>{{ $totalMarks }}</td>
            <td>{{ $correctAnswers }}</td>
            <td>{{ $attempt->quiz->questions->count() }}</td>
            <td>{{ getGrade($earnedMarks, $totalMarks) }}</td>
        </tr>
    </tbody>
</table>

<h4>Question Review</h4>
@foreach ($questionsWithAnswers as $index => $item)
    <p><strong>Q{{ $index + 1 }}: {{ $item['question'] }}</strong></p>
    <p>
        <strong>Your Answer:</strong>
        @if($item['questionType'] === 'multiple_choice')
            @foreach(explode(',', $item['studentAnswer']) as $answer)
                <span>{{ $answer }}</span>
            @endforeach
        @else
            {{ $item['studentAnswer'] }}
        @endif
    </p>

    <p><strong>Correct Answer:</strong>
        @if($item['questionType'] === 'multiple_choice')
            @foreach(explode(',', $item['correctAnswer']) as $answer)
                <span>{{ $answer }}</span>
            @endforeach
        @else
            {{ $item['correctAnswer'] }}
        @endif
    </p>

    @if($item['questionType'] === 'open_ended')
        <p><strong>Marks Awarded:</strong> {{ $item['awardedMark'] }} / {{ $item['maxMark'] }}</p>
    @endif

    <hr>
@endforeach

</body>
</html>
