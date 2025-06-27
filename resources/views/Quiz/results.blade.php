@extends('Layouts.app')

@section('title', 'Quiz Results')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="quiz-container">
                <div class="quiz-header">
                    <h1 class="quiz-title">
                        <i class="fas fa-trophy me-3"></i>Quiz Results
                    </h1>
                    <p class="quiz-subtitle">Your performance summary, {{ session('user_name') }}!</p>
                </div>
                <div class="quiz-body">
                    <div id="results-screen">
                        <div class="results-card">
                            <div class="text-center mb-4">
                                <i class="fas fa-chart-pie text-primary" style="font-size: 4rem;"></i>
                            </div>
                            <h3 class="text-center mb-4" id="score-message"></h3>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            <h5 class="card-title">Correct Answers</h5>

                                            <p class="card-text display-6 text-success" id="correct-count">
                                                {{ $results['correct_answers'] ?? 0 }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            <h5 class="card-title">Incorrect Answers</h5>
                                            <p class="card-text display-6 text-danger" id="wrong-count">
                                                {{ $results['wrong_answers'] ?? 0 }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            <h5 class="card-title">Skipped Questions</h5>
                                            <p class="card-text display-6 text-warning" id="skipped-count">
                                                {{ $results['skipped_answers'] ?? 0 }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            <h5 class="card-title">Score</h5>
                                            <p class="card-text display-6 text-primary" id="percentage-score">
                                                {{ $results['percentage'] ?? 0 }}%</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-4">
                                <button id="reset-btn" class="btn btn-primary btn-lg">
                                    <i class="fas fa-redo me-2"></i>Try Again
                                </button>
                                <button id="view-history-btn" class="btn btn-secondary btn-lg ms-2">
                                    <i class="fas fa-history me-2"></i>View History
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="loading-screen" class="loading-spinner">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Loading results...</p>
                    </div>
                    <div id="alerts-container"></div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                window.quizApp.loadResults();
            });
        </script>
        <script>
            $(document).ready(function() {
                $('#reset-btn').on('click', function() {
                    $.ajax({
                        url: "{{ route('api.quiz.reset') }}",
                        type: "POST",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
                        },
                        success(res) {
                            if (res.success) {
                                window.location.href = res.redirect; // /quiz
                            } else {
                                alert(res.message || 'Reset failed');
                            }
                        },
                        error() {
                            alert('Server error while resetting.');
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
