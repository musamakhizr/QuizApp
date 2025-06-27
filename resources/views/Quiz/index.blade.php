@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="quiz-container">
                <div class="quiz-header">
                    <h1 class="quiz-title">
                        <i class="fas fa-code me-3"></i>PHP Quiz Challenge
                    </h1>
                    <p class="quiz-subtitle">Test your PHP knowledge with 5 challenging questions</p>
                </div>
                <div class="quiz-body">

                        <div id="question-screen" style="{{ session('user_id') ? '' : 'display:none' }}">
                            <div class="progress-container">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold">Progress</span>
                                    <span id="progress-text" class="text-muted">Question 1 of 5</span>
                                </div>
                                <div class="progress">
                                    <div id="progress-bar" class="progress-bar" role="progressbar" style="width: 20%"></div>
                                </div>
                            </div>
                            <div class="question-card">
                                <h4 id="question-text" class="question-text"></h4>
                                <div id="answers-container"></div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button id="skip-btn" class="btn btn-secondary">
                                    <i class="fas fa-forward me-2"></i>Skip Question
                                </button>
                                <button id="next-btn" class="btn btn-primary" disabled>
                                    <i class="fas fa-arrow-right me-2"></i>Next Question
                                </button>
                            </div>
                        </div>


                            <div id="welcome-screen" class="text-center" style="{{ session('user_id') ? 'display:none' : '' }}">
                            <div class="mb-4">
                                <i class="fas fa-user-circle text-primary" style="font-size: 4rem;"></i>
                            </div>
                            <h3 class="mb-4">Welcome to the PHP Quiz!</h3>
                            <p class="text-muted mb-4">Enter your name to begin the challenge. You'll have 5 questions to test your PHP knowledge.</p>
                            <form id="start-form" class="row g-3 justify-content-center" action="{{ route('api.quiz.start') }}" novalidate>
                                @csrf
                                <div class="col-md-8">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="user-name" name="name" required pattern="[a-zA-Z\s]+" maxlength="100">
                                        <label for="user-name"><i class="fas fa-user me-2"></i>Your Name</label>
                                        <div class="invalid-feedback">Please enter a valid name (letters and spaces only, max 100 characters).</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-play me-2"></i>Start Quiz
                                    </button>
                                </div>
                            </form>
                        </div>

                    <div id="loading-screen" class="loading-spinner">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Loading next question...</p>
                    </div>
                    <div id="alerts-container"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
