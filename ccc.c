#include <stdio.h>
#include <math.h>
#include <time.h>

int main() {
    long iterations = 1000000000L;
    double result = 0.0;

    // Get the start time
    struct timespec start, end;
    clock_gettime(CLOCK_MONOTONIC, &start);

    // Perform the calculations
    for (long i = 0; i < iterations; i++) {
        result += sin(i) * cos(i);
    }

    // Get the end time
    clock_gettime(CLOCK_MONOTONIC, &end);

    // Calculate the duration in seconds
    double duration = (end.tv_sec - start.tv_sec) + (end.tv_nsec - start.tv_nsec) / 1e9;

    printf("Time taken for %ld iterations: %f seconds\n", iterations, duration);

    return 0;
}
