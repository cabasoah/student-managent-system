@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu') <!-- Left Sidebar -->

        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi {{ isset($question) ? 'bi-pencil-square' : 'bi-plus-circle' }}"></i>
                        {{ isset($question) ? 'Edit' : 'Add' }} Question - {{ $quiz->title }}
                    </h1>

                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.index') }}">Quizzes</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.questions', $quiz->id) }}">Questions</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ isset($question) ? 'Edit' : 'Add' }} Question</li>
                        </ol>
                    </nav>

                    <div class="bg-white p-3 border shadow-sm">
                        <form action="{{ isset($question) ? route('admin.quizzes.questions.update', [$quiz->id, $question->id]) : route('admin.quizzes.questions.store', $quiz->id) }}" method="POST">
                            @csrf
                            @isset($question)
                                @method('PUT')
                            @endisset

                            <!-- Question Text -->
                            <div class="mb-3">
                                <label for="question_text" class="form-label">Question Text</label>
                                <textarea name="question_text" class="form-control" rows="3" required>{{ old('question_text', $question->question_text ?? '') }}</textarea>
                            </div>

                            <!-- Question Type -->
                            <div class="mb-3">
                                <label for="question_type" class="form-label">Question Type</label>
                                <select name="question_type" class="form-select" id="question_type" required>
                                    <option value="" disabled {{ !isset($question) ? 'selected' : '' }}>Select Question Type</option>
                                    <option value="single_choice" {{ old('question_type', $question->type ?? '') == 'single_choice' ? 'selected' : '' }}>Single Choice</option>
                                    <option value="multiple_choice" {{ old('question_type', $question->type ?? '') == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                                    <option value="open_ended" {{ old('question_type', $question->type ?? '') == 'open_ended' ? 'selected' : '' }}>Open-Ended</option>
                                </select>
                            </div>

                            <!-- Options (for Single/Multiple Choice) -->
                            <div id="options-container" class="mb-3 {{ isset($question) && ($question->type == 'single_choice' || $question->type == 'multiple_choice') ? '' : 'd-none' }}">
                                <label class="form-label">Options (Mark correct answers)</label>
                                <div id="options-list">
                                    @if(isset($question) && ($question->type == 'single_choice' || $question->type == 'multiple_choice'))
                                        @foreach($question->options as $index => $option)
                                            <div class="input-group mb-2">
                                                <input type="text" name="options[{{ $index }}][text]" class="form-control" placeholder="Option text" value="{{ old('options.'.$index.'.text', $option->option_text) }}">
                                                <div class="input-group-text">
                                                    <input type="checkbox" name="options[{{ $index }}][is_correct]" value="1" {{ $option->is_correct ? 'checked' : '' }}>
                                                </div>
                                                <button type="button" class="btn btn-danger btn-sm remove-option">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="add-option">
                                    <i class="bi bi-plus"></i> Add Option
                                </button>
                            </div>

                            <!-- Open-Ended Correct Answer and Marks -->
                            <div id="open-ended-fields" class="mb-3 {{ isset($question) && $question->type == 'open_ended' ? '' : 'd-none' }}">
                                <label for="correct_answer" class="form-label">Correct Answer (Optional for Auto-Grading)</label>
                                <textarea name="correct_answer" id="correct_answer" class="form-control" rows="2">{{ old('correct_answer', $question->correct_answer ?? '') }}</textarea>

                                <label for="max_mark" class="form-label mt-3">Maximum Marks</label>
                                <input type="number" name="max_mark" id="max_mark" class="form-control" value="{{ old('max_mark', $question->max_mark ?? 0) }}" min="0">
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-save"></i> {{ isset($question) ? 'Update' : 'Save' }} Question
                            </button>
                            <a href="{{ route('admin.quizzes.questions', $quiz->id) }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Cancel
                            </a>
                        </form>
                    </div>
                </div>
            </div>

            @include('layouts.footer') <!-- Footer -->
        </div>
    </div>
</div>

<!-- Script for Dynamic Fields -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const questionType = document.getElementById('question_type');
        const optionsContainer = document.getElementById('options-container');
        const openEndedFields = document.getElementById('open-ended-fields');
        const optionsList = document.getElementById('options-list');
        const addOptionBtn = document.getElementById('add-option');

        // Handle question type change
        questionType.addEventListener('change', function () {
            if (this.value === 'single_choice' || this.value === 'multiple_choice') {
                optionsContainer.classList.remove('d-none');
                openEndedFields.classList.add('d-none');
                if (optionsList.children.length === 0) {
                    addDefaultOptions();
                }
            } else if (this.value === 'open_ended') {
                optionsContainer.classList.add('d-none');
                openEndedFields.classList.remove('d-none');
                optionsList.innerHTML = ''; // Remove existing options
            } else {
                optionsContainer.classList.add('d-none');
                openEndedFields.classList.add('d-none');
                optionsList.innerHTML = ''; // Remove existing options
            }
        });

        // Function to add default options when type is selected
        function addDefaultOptions() {
            for (let i = 0; i < 2; i++) { 
                addOption();
            }
        }

        // Function to add a new option dynamically
        function addOption() {
            const index = optionsList.children.length;
            const optionDiv = document.createElement('div');
            optionDiv.classList.add('input-group', 'mb-2');
            optionDiv.innerHTML = `
                <input type="text" name="options[${index}][text]" class="form-control" placeholder="Option text" required>
                <div class="input-group-text">
                    <input type="checkbox" name="options[${index}][is_correct]" value="1">
                </div>
                <button type="button" class="btn btn-danger btn-sm remove-option">
                    <i class="bi bi-x"></i>
                </button>
            `;
            optionsList.appendChild(optionDiv);

            optionDiv.querySelector('.remove-option').addEventListener('click', function () {
                optionDiv.remove();
            });
        }

        addOptionBtn.addEventListener('click', addOption);
    });
</script>

@endsection
