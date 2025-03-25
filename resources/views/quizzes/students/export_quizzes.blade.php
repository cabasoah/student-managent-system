<table>
    <thead>
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Duration (mins)</th>
            <th>Total Marks</th>
            <th>Student Score</th>
            <th>Grade</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($quizzes as $quiz)
        <tr>
            <td>{{ $quiz->title }}</td>
            <td>{{ $quiz->description }}</td>
            <td>{{ $quiz->duration }}</td>
            <td>{{ $quiz->total_marks }}</td>
            <td>{{ $quiz->student_score }}</td>
            <td>{{ getGrade($quiz->student_score, $quiz->total_marks) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
