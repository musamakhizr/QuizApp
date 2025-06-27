<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PHP Quiz App - @yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="{{ route('quiz.index') }}">PHP Quiz App</a>
            @if (session('user_id'))
                <span class="navbar-text ms-auto">Welcome, {{ session('user_name') }}</span>
            @endif
        </div>
    </nav>
    <div class="container-fluid py-5">
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show slide-in" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show slide-in" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @yield('content')
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    {{-- <script src="{{ asset('js/quiz.js') }}"></script> --}}
    <script>
        $(document).ready(function() {
            class QuizApp {
                constructor() {
                    this.currentQuestion = null;
                    this.selectedAnswer = null;
                    this.currentQuestionNumber = 1;
                    this.totalQuestions = 5;

                    this.init();
                }

                init() {
                    this.setupAjaxDefaults();
                    this.setupEventListeners();
                    if ($('#question-screen').length) {
                        this.checkExistingSession();
                    }
                }

                setupAjaxDefaults() {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        error: (xhr, status, error) => {
                            console.error('AJAX Error:', xhr.status, error, xhr.responseJSON);
                            let message = 'An error occurred. Please try again.';
                            if (xhr.status === 405) {
                                message = 'Invalid request method. Please try again.';
                            } else if (xhr.status === 419) {
                                message = 'Session expired. Please refresh the page.';
                            } else if (xhr.responseJSON) {
                                message = xhr.responseJSON.errors
                                    ? `<ul>${Object.values(xhr.responseJSON.errors).flat().map(err => `<li>${err}</li>`).join('')}</ul>`
                                    : xhr.responseJSON.message || message;
                            }
                            this.showAlert(message, 'danger');
                            this.hideLoading();
                        }
                    });
                }

                setupEventListeners() {
                    // Start quiz form submission
                    $('#start-form').on('submit', (e) => {
                        e.preventDefault();
                        const form = $(e.target);
                        const name = $('#user-name').val().trim();
                        if (!form[0].checkValidity()) {
                            form.addClass('was-validated');
                            this.showAlert('Please enter a valid name.', 'warning');
                            return;
                        }
                        this.startQuiz();
                    });

                    // Answer selection
                    $(document).on('click', '.answer-option', (e) => {
                        this.selectAnswer($(e.currentTarget));
                    });

                    // Next button
                    $('#next-btn').on('click', () => {
                        this.submitAnswer();
                    });

                    // Skip button
                    $('#skip-btn').on('click', () => {
                        this.skipQuestion();
                    });

                    // View history button
                    $('#view-history-btn').on('click', () => {
                        this.viewHistory();
                    });

                    // Input validation for name field
                    $('#user-name').on('input', function() {
                        const name = $(this).val().trim();
                        const isValid = name.length >= 2 && /^[a-zA-Z\s]+$/.test(name);
                        $(this).toggleClass('is-valid', isValid).toggleClass('is-invalid', !isValid);
                    });
                }

                checkExistingSession() {
                    this.makeRequest('{{ route('api.quiz.question') }}', 'GET')
                        .then(response => {
                            console.log('Check Session Response:', response);
                            if (response.success && !response.completed) {
                                this.showQuestionScreen();
                                this.loadQuestion();
                                console.log('Question Loaded');
                            } else if (response.completed) {
                                window.location.href = '{{ route('quiz.results') }}';
                            }
                        })
                        .catch(() => {
                            console.error('Check Session Error:', error);
                            this.showWelcomeScreen();
                        });
                }

                startQuiz() {
                    const name = $('#user-name').val().trim();
                    this.showLoading();
                    this.makeRequest('{{ route('api.quiz.start') }}', 'POST', {
                            name
                        })
                        .then(response => {
                            console.log('Start Quiz Response:', response);
                            if (response.success) {
                                console.log('Success Quiz Response:', response);
                                this.userId = response.user.id; // Assign user ID from response
                                console.log('User ID set to:', this.userId);
                                //this.showAlert(`Welcome ${response.user.name}! Let's start the quiz.`, 'success')
                                this.showQuestionScreen();
                                this.loadQuestion();

                            } else {
                                this.showAlert(response.message || 'Failed to start quiz.', 'danger');
                                this.showWelcomeScreen();
                            }
                        })
                        .always(() => {
                            console.log('Start Quiz Cleanup');
                            this.hideLoading();
                        });
                }

                loadQuestion() {
                    // if (!this.userId) {
                    //     this.showAlert('No user session. Please start the quiz.', 'danger');
                    //     this.showWelcomeScreen();
                    //     return;
                    // }
                    this.showLoading();
                    this.makeRequest('{{ route('api.quiz.question') }}', 'GET')
                        .then(response => {
                            console.log('Load Question Response:', response);
                            if (!response || typeof response !== 'object') {
                                throw new Error('Invalid response format.');
                            }
                            if (response.success) {
                                if (response.completed) {
                                    this.showAlert('Quiz completed!', 'success');
                                    setTimeout(() => {
                                        window.location.href = '{{ route('quiz.results') }}';
                                    }, 1500);
                                } else {
                                    if (!response.question || !response.progress) {
                                        throw new Error('Missing question or progress data.');
                                    }
                                    this.showQuestionScreen();
                                    this.displayQuestion(response.question);
                                    this.updateProgress(response.progress);
                                }
                            } else {
                                this.showAlert(response.message || 'Failed to load question.', 'danger');
                            }
                        })
                        .catch(error => {
                            console.error('Load Question Error:', error);
                            this.showAlert('Error loading question: ' + error.message, 'danger');
                        })
                        .always(() => {
                            console.log('Load Question Cleanup');
                            this.hideLoading();
                            // this.showQuestionScreen();
                        });
                }

                displayQuestion(question) {
                    if (!question || !question.id || !question.text || !Array.isArray(question.answers)) {
                        throw new Error('Invalid question data.');
                    }
                    this.currentQuestion = question;
                    this.selectedAnswer = null;
                    $('#question-text').text(question.text);
                    const answersHtml = question.answers.map(answer => {
                        if (!answer.id || !answer.text) {
                            throw new Error('Invalid answer data.');
                        }
                        return `
                            <div class="answer-option" data-answer-id="${answer.id}">
                                <div class="d-flex align-items-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="answer" value="${answer.id}">
                                        <label class="form-check-label flex-grow-1 ms-2">
                                            ${answer.text}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('');
                    $('#answers-container').html(answersHtml);
                    $('#next-btn').prop('disabled', true);
                    $('#question-screen .question-card').addClass('fade-in');
                }

                updateProgress(progress) {
                    if (!progress || !progress.current || !progress.total) {
                        throw new Error('Invalid progress data.');
                    }
                    this.currentQuestionNumber = progress.current;
                    this.totalQuestions = progress.total;
                    $('#progress-text').text(`Question ${progress.current} of ${progress.total}`);
                    const percentage = (progress.current / progress.total) * 100;
                    $('#progress-bar').css('width', `${percentage}%`).attr('aria-valuenow', percentage);
                }

                selectAnswer($option) {
                    $('.answer-option').removeClass('selected');
                    $('.answer-option input[type="radio"]').prop('checked', false);
                    $option.addClass('selected');
                    $option.find('input[type="radio"]').prop('checked', true);
                    this.selectedAnswer = $option.data('answer-id');
                    $('#next-btn').prop('disabled', false);
                }

                submitAnswer() {
                    if (!this.selectedAnswer) {
                        this.showAlert('Please select an answer.', 'warning');
                        return;
                    }
                    this.showLoading();
                    const data = {
                        question_id: this.currentQuestion.id,
                        answer_id: this.selectedAnswer
                    };
                    this.makeRequest('{{ route('api.quiz.answer') }}', 'POST', data)
                        .then(response => {
                            if (response.success) {
                                if (response.quiz_completed) {
                                    this.showAlert('Quiz completed!', 'success');
                                    setTimeout(() => {
                                        window.location.href = '{{ route('quiz.results') }}';
                                    }, 1500);
                                } else {
                                    const message = response.result.is_correct ? 'Correct answer! 🎉' :
                                        'Incorrect answer. 😞';
                                    this.showAlert(message, response.result.is_correct ? 'success' :
                                        'warning');
                                    setTimeout(() => {
                                        this.loadQuestion();
                                    }, 1500);
                                }
                            } else {
                                this.showAlert(response.message || 'Failed to submit answer.', 'danger');
                            }
                        })
                        .always(() => {
                            console.log('Submit Answer Cleanup');
                            this.hideLoading();
                        });
                }

                skipQuestion() {
                    this.showLoading();
                    const data = {
                        question_id: this.currentQuestion.id,
                        answer_id: null
                    };
                    this.makeRequest('{{ route('api.quiz.answer') }}', 'POST', data)
                        .then(response => {
                            if (response.success) {
                                if (response.quiz_completed) {
                                    this.showAlert('Quiz completed!', 'success');
                                    setTimeout(() => {
                                        window.location.href = '{{ route('quiz.results') }}';
                                    }, 1500);
                                } else {
                                    this.showAlert('Question skipped.', 'info');
                                    setTimeout(() => {
                                        this.loadQuestion();
                                    }, 1000);
                                }
                            } else {
                                this.showAlert(response.message || 'Failed to skip question.', 'danger');
                            }
                        })
                        .always(() => {
                            console.log('Skip Question Cleanup');
                            this.hideLoading();
                        });
                }

                loadResults() {
                    this.showLoading();
                    this.makeRequest('{{ route('api.quiz.results') }}', 'GET')
                        .then(response => {
                            console.log('Load Results Response:', response);
                            if (response.success) {
                                this.displayResults(response.results);
                            } else {
                                this.showAlert('Failed to load results.', 'danger');
                            }
                        })
                        .always(() => {
                            console.log('Load Results Cleanup');
                            this.hideLoading();
                        });
                }

                displayResults(results) {
                    $('#correct-count').text(results.correct_answers);
                    $('#wrong-count').text(results.wrong_answers);
                    $('#skipped-count').text(results.skipped_answers);
                    $('#percentage-score').text(results.percentage);
                    let message = '';
                    if (results.percentage >= 80) {
                        message = 'Excellent! 🌟';
                    } else if (results.percentage >= 60) {
                        message = 'Good job! 👍';
                    } else if (results.percentage >= 40) {
                        message = 'Not bad! 👌';
                    } else {
                        message = 'Keep practicing! 💪';
                    }
                    $('#score-message').text(message);
                    $('#results-screen').addClass('fade-in');
                }

                resetQuiz() {
                    this.showLoading();
                    this.makeRequest('{{ route('api.quiz.reset') }}', 'POST')
                        .then(response => {
                            console.log('Reset Quiz Response:', response);
                            if (response.success) {
                                this.showAlert('Quiz reset successfully. Starting a new quiz...', 'success');
                                setTimeout(() => {
                                    window.location.href = response.redirect;
                                }, 1500);
                            } else {
                                this.showAlert(response.message || 'Failed to reset quiz.', 'danger');
                            }
                        })
                        .always(() => {
                            console.log('Reset Quiz Cleanup');
                            this.hideLoading();
                        });
                }
                viewHistory() {
                    this.showAlert('History feature coming soon!', 'info');
                }

                makeRequest(url, method, data = null) {
                    const options = {
                        url: url,
                        method: method,
                        dataType: 'json'
                    };
                    if (data && (method === 'POST' || method === 'PUT')) {
                        options.data = JSON.stringify(data);
                    }
                    return $.ajax(options);
                }

                showAlert(message, type = 'info') {
                    const alertHtml = `
                        <div class="alert alert-${type} alert-dismissible fade show slide-in" role="alert">
                            <i class="fas fa-${this.getAlertIcon(type)} me-2"></i>
                            ${message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    $('#alerts-container').html(alertHtml);
                    setTimeout(() => {
                        $('.alert').alert('close');
                    }, 1000);
                }

                getAlertIcon(type) {
                    const icons = {
                        'success': 'check-circle',
                        'danger': 'exclamation-triangle',
                        'warning': 'exclamation-circle',
                        'info': 'info-circle'
                    };
                    return icons[type] || 'info-circle';
                }

                showLoading() {
                    $('#loading-screen').show().addClass('fade-in');
                    //$('#question-screen, #welcome-screen, #results-screen').hide();
                }

                hideLoading() {
                    $('#loading-screen').hide();
                }

                showWelcomeScreen() {
                    $('#welcome-screen').show().addClass('fade-in');
                    $('#question-screen, #results-screen').hide();
                    $('#user-name').focus();
                }

                showQuestionScreen() {
                    $('#question-screen').show().addClass('slide-in');
                    $('#welcome-screen, #results-screen').hide();
                }

                showResultsScreen() {
                    $('#results-screen').show().addClass('fade-in');
                    $('#welcome-screen, #question-screen').hide();
                }
            }

            window.quizApp = new QuizApp();
        });
    </script>
    @stack('scripts')

</body>

</html>
